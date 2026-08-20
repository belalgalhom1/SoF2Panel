<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;
use App\Http\Controllers\WebRconController;

class ServerPlayerController extends Controller
{
    public function index(Server $server)
    {
        $user = auth()->user();
        
        // Ensure user has access
        if (!$user->isAdmin()) {
            $hasAccess = $user->servers()->where('server_id', $server->id)->first();
            if (!$hasAccess) {
                abort(403);
            }
        }

        return view('servers.players', compact('server'));
    }

    public function list(Server $server)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $hasAccess = $user->servers()->where('server_id', $server->id)->first();
            if (!$hasAccess) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        }

        $rconController = app(WebRconController::class);
        
        $host = $server->host->hostname;
        $port = $server->port;
        $password = \Illuminate\Support\Facades\Crypt::decryptString($server->rcon_password);

        try {
            $response = $rconController->sendRconCommand($host, $port, $password, 'status');
            
            $cleaned = preg_replace('/\^[0-9]/', '', $response);
            $cleaned = preg_replace('/^\xff\xff\xff\xffprint\n/', '', $cleaned);
            
            $players = $this->parseStatusOutput($cleaned);
            
            return response()->json([
                'success' => true,
                'players' => $players
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to connect to RCON: ' . $e->getMessage()
            ], 500);
        }
    }

    private function parseStatusOutput($output)
    {
        $players = [];
        $lines = explode("\n", trim($output));
        
        $inPlayerList = false;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (str_starts_with($line, '--') || str_starts_with($line, '---')) {
                $inPlayerList = true;
                continue;
            }

            if ($inPlayerList) {
                if (preg_match('/^(\d+)\s+(-?\d+)\s+(\d+)\s+(.+?)\s+(\S+)(?:\s+(\d+))?$/', $line, $matches)) {
                    $players[] = [
                        'num' => $matches[1],
                        'score' => $matches[2],
                        'ping' => $matches[3],
                        'name' => trim($matches[4]),
                        'address' => $matches[5]
                    ];
                }
            }
        }

        return $players;
    }
}
