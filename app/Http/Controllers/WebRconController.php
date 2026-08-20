<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Server;
use Illuminate\Support\Facades\Crypt;

class WebRconController extends Controller
{
    protected function checkPermission(Server $server)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $pivot = $server->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !$pivot->use_web_rcon) {
                abort(403);
            }
        }
    }

    public function index(Server $server)
    {
        $this->checkPermission($server);
        return view('servers.rcon', compact('server'));
    }

    public function execute(Request $request, Server $server)
    {
        $this->checkPermission($server);
        
        $request->validate(['command' => 'required|string']);
        $command = $request->input('command');
        
        $host = $server->host->hostname;
        $port = $server->port;
        
        $password = $server->rcon_password;
        try {
            $password = Crypt::decryptString($server->rcon_password);
        } catch (\Exception $e) {
            $password = $server->rcon_password;
        }
        
        $response = $this->sendRconCommand($host, $port, $password, $command);
        
        $cleaned = preg_replace('/\^[0-9]/', '', $response);

        $cleaned = preg_replace('/^\xff\xff\xff\xffprint\n/', '', $cleaned);

        if (empty(trim($response))) {
            $response = "Command sent (no output).";
        }

        return response()->json([
            'success' => true,
            'response' => trim($cleaned),
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    }

    public function sendRconCommand($ip, $port, $password, $command)
    {
        $socket = @fsockopen("udp://$ip", $port, $errno, $errstr, 2);
        
        if (!$socket) {
            return "Connection failed: $errstr ($errno)";
        }

        stream_set_timeout($socket, 1);

        $packet = "\xff\xff\xff\xffrcon \"$password\" $command";
        fwrite($socket, $packet);

        $response = "";
        $startTime = microtime(true);
        $firstRead = true;

        while (true) {
            $data = fread($socket, 4096);
            
            if (empty($data) && $firstRead) {
                return "Error: Server offline or timeout.";
            }
            
            if (empty($data)) {
                break;
            }
            
            $firstRead = false;
            $response .= $data;
            
            if ((microtime(true) - $startTime) > 2) {
                break; 
            }
        }

        fclose($socket);
        return $response;
    }
}
