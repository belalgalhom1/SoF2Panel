<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Services\ServerService;
use Illuminate\Http\Request;

class ServerApiController extends Controller
{
    protected $serverService;

    public function __construct(ServerService $serverService)
    {
        $this->serverService = $serverService;
    }

    public function index()
    {
        $user = auth()->user();
        
        if ($user->isAdmin()) {
            $servers = Server::with(['game', 'host'])->get();
        } else {
            $servers = $user->servers()->with(['game', 'host'])->get();
        }

        $servers->makeHidden(['ftp_password', 'rcon_password']);

        \App\Models\Log::create([
            'user_id' => $user->id,
            'action' => 'API: Get All Servers',
            'target' => 'All Servers',
            'ip' => request()->ip()
        ]);

        return response()->json([
            'success' => true,
            'data' => $servers
        ]);
    }

    public function show(Server $server)
    {
        $user = auth()->user();
        
        if (!$user->isAdmin()) {
            if (!$server->users()->where('user_id', $user->id)->exists()) {
                return response()->json(['error' => 'Permission denied.'], 403);
            }
        }

        $server->load(['game', 'host']);
        
        $server->makeHidden(['ftp_password', 'rcon_password']);

        \App\Models\Log::create([
            'user_id' => $user->id,
            'action' => 'API: Get Server Info',
            'target' => $server->name,
            'ip' => request()->ip()
        ]);

        return response()->json([
            'success' => true,
            'data' => $server
        ]);
    }

    public function start(Server $server)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $pivot = $server->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !$pivot->start_server) {
                return response()->json(['error' => 'Permission denied.'], 403);
            }
        }

        try {
            $this->serverService->startServer($server);
            $server->update(['expected_state' => 'online']);

            \App\Models\Log::create([
                'user_id' => $user->id,
                'action' => 'API: Start Server',
                'target' => $server->name,
                'ip' => request()->ip()
            ]);

            return response()->json(['success' => true, 'message' => 'Server started successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to start server: ' . $e->getMessage()], 500);
        }
    }

    public function stop(Server $server)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $pivot = $server->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !$pivot->stop_server) {
                return response()->json(['error' => 'Permission denied.'], 403);
            }
        }

        try {
            $this->serverService->stopServer($server);
            $server->update(['expected_state' => 'offline']);

            \App\Models\Log::create([
                'user_id' => $user->id,
                'action' => 'API: Stop Server',
                'target' => $server->name,
                'ip' => request()->ip()
            ]);

            return response()->json(['success' => true, 'message' => 'Server stopped successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to stop server: ' . $e->getMessage()], 500);
        }
    }

    public function restart(Server $server)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $pivot = $server->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !$pivot->stop_server || !$pivot->start_server) {
                return response()->json(['error' => 'Permission denied (needs start and stop).'], 403);
            }
        }

        try {
            $this->serverService->stopServer($server);
            sleep(1);
            $this->serverService->startServer($server);
            $server->update(['expected_state' => 'online']);

            \App\Models\Log::create([
                'user_id' => $user->id,
                'action' => 'API: Restart Server',
                'target' => $server->name,
                'ip' => request()->ip()
            ]);

            return response()->json(['success' => true, 'message' => 'Server restarted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to restart server: ' . $e->getMessage()], 500);
        }
    }

    public function rcon(Request $request, Server $server)
    {
        $user = auth()->user();
        
        if (!$user->isAdmin()) {
            $pivot = $server->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !$pivot->use_web_rcon) {
                return response()->json(['error' => 'Permission denied (needs use_web_rcon).'], 403);
            }
        }

        $request->validate([
            'command' => 'required|string'
        ]);

        $command = $request->input('command');
        $host = $server->host->hostname;
        $port = $server->port;

        $password = $server->rcon_password;
        try {
            $password = \Illuminate\Support\Facades\Crypt::decryptString($server->rcon_password);
        } catch (\Exception $e) {
            $password = $server->rcon_password;
        }

        try {
            $rconController = app(\App\Http\Controllers\WebRconController::class);
            $rconResponse = $rconController->sendRconCommand($host, $port, $password, $command);
            
            $cleaned = preg_replace('/\^[0-9]/', '', $rconResponse);
            $cleaned = preg_replace('/^\xff\xff\xff\xffprint\n/', '', $cleaned);

            if (empty(trim($rconResponse))) {
                $cleaned = "Command sent (no output).";
            }

            \App\Models\Log::create([
                'user_id' => $user->id,
                'action' => 'API: Send RCON Command',
                'target' => $server->name,
                'ip' => request()->ip()
            ]);

            return response()->json([
                'success' => true,
                'response' => trim($cleaned)
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to execute RCON command: ' . $e->getMessage()], 500);
        }
    }
}
