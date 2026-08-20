<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Server;
use App\Models\Host;
use App\Models\Game;
use App\Services\ServerService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class ServerController extends Controller
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
            $servers = Server::with(['host', 'game'])->get();
        } else {
            $servers = $user->servers()->with(['host', 'game'])->get();
        }
        
        return view('servers.index', compact('servers'));
    }

    public function create()
    {
        $hosts = Host::all();
        $games = Game::all();
        return view('servers.create', compact('hosts', 'games'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'host_id' => 'required|exists:hosts,id',
                'game_id' => 'required|exists:games,id',
                'port' => 'required|integer|min:1024|max:65535',
                'port_gold' => 'nullable|integer|min:1024|max:65535',
                'ftp_username' => 'required|string|max:255|unique:servers',
                'max_clients' => 'required|integer|min:1|max:128',
                'rcon_password' => 'required|string',
                'ftp_password' => 'nullable|string',
            ]);

            $ftpPassword = $request->ftp_password ?: Str::random(12);

            $game = Game::findOrFail($request->game_id);

            $port = $request->port;
            $port_gold = $request->port_gold;

            $portError = $this->checkPortConflict($request->host_id, $request->port, $request->port_gold);
            if ($portError) {
                return back()->with('error', $portError)->withInput();
            }

            $server = Server::create([
                'name' => $request->name,
                'host_id' => $request->host_id,
                'game_id' => $request->game_id,
                'start_script' => $game->start_script,
                'port' => $request->port,
                'port_gold' => $request->port_gold,
                'ftp_username' => $request->ftp_username,
                'ftp_password' => Crypt::encryptString($ftpPassword),
                'max_clients' => $request->max_clients,
                'rcon_password' => Crypt::encryptString($request->rcon_password),
                'is_active' => false,
                'auto_restart' => true,
            ]);

            \App\Jobs\ProvisionServerJob::dispatchSync($server, $ftpPassword);
            return redirect()->route('servers.index')->with('success', 'Server created and provisioned successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create server: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Server $server)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $this->serverService->destroyServer($server);

        $server->delete();
        return redirect()->route('servers.index')->with('success', 'Server deleted successfully.');
    }

    public function show(Server $server)
    {
        $user = auth()->user();
        
        if (!$user->isAdmin()) {
            $pivot = $server->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !$pivot->view_server) {
                abort(403);
            }
        }

        $status = $this->serverService->getServerStatus($server);
        $users = \App\Models\User::whereNotIn('role', ['admin', 'owner'])->get();
        return view('servers.show', compact('server', 'status', 'users'));
    }

    private function checkSettingsAccess(Server $server)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $pivot = $server->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !$pivot->manage_settings) {
                abort(403);
            }
        }
    }

    public function settings(Server $server)
    {
        $this->checkSettingsAccess($server);

        return view('servers.settings', compact('server'));
    }

    public function start(Server $server)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $pivot = $server->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !$pivot->start_server) abort(403);
        }

        try {
            $this->serverService->startServer($server);
            
            $server->update(['expected_state' => 'online']);

            \App\Models\Log::create([
                'user_id' => $user->id,
                'action' => 'Start Server',
                'target' => $server->name,
                'ip' => request()->ip()
            ]);

            return back()->with('success', 'Server started successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to start server: ' . $e->getMessage());
        }
    }

    public function stop(Server $server)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $pivot = $server->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !$pivot->stop_server) abort(403);
        }

        try {
            $this->serverService->stopServer($server);
            
            $server->update(['expected_state' => 'offline']);

            \App\Models\Log::create([
                'user_id' => $user->id,
                'action' => 'Stop Server',
                'target' => $server->name,
                'ip' => request()->ip()
            ]);

            return back()->with('success', 'Server stopped successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to stop server: ' . $e->getMessage());
        }
    }

    public function toggleAutoRestart(Server $server)
    {
        $this->checkSettingsAccess($server);
        $user = auth()->user();

        $server->update([
            'auto_restart' => !$server->auto_restart
        ]);

        $status = $server->auto_restart ? 'Enabled' : 'Disabled';
        
        \App\Models\Log::create([
            'user_id' => auth()->user()->id,
            'action' => 'Toggled Auto-Restart',
            'target' => "{$status} auto-restart for {$server->name}",
            'ip' => request()->ip()
        ]);

        return back()->with('success', "Auto-restart has been {$status}.");
    }

    public function updateBackupSettings(Request $request, Server $server)
    {
        $this->checkSettingsAccess($server);

        $request->validate([
            'auto_backup' => 'required|boolean',
            'backup_interval' => 'required|integer|in:60,180,360,720,1440,2880,4320,10080'
        ]);

        $server->update([
            'auto_backup' => $request->auto_backup,
            'backup_interval' => $request->backup_interval
        ]);

        \App\Models\Log::create([
            'user_id' => auth()->user()->id,
            'action' => 'Updated Backup Settings',
            'target' => "Updated auto-backup settings for {$server->name}",
            'ip' => request()->ip()
        ]);

        return back()->with('success', "Backup settings updated successfully.");
    }

    public function updateScript(Request $request, Server $server)
    {
        $this->checkSettingsAccess($server);
        $user = auth()->user();

        $request->validate([
            'start_script' => 'required|string',
        ]);

        $server->update([
            'start_script' => $request->start_script,
        ]);

        $status = $this->serverService->getServerStatus($server);
        if ($status === 'Running') {
            try {
                $this->serverService->stopServer($server);
                sleep(1);
                $this->serverService->startServer($server);
                $server->update(['expected_state' => 'online']);
            } catch (\Exception $e) {
                return back()->with('error', 'Script saved, but server failed to restart: ' . $e->getMessage());
            }
        }

        \App\Models\Log::create([
            'user_id' => $user->id,
            'action' => 'Update Start Script',
            'target' => $server->name,
            'ip' => request()->ip()
        ]);

        return back()->with('success', 'Start script updated successfully.');
    }

    public function updateGeneral(Request $request, Server $server)
    {
        $this->checkSettingsAccess($server);
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'max_clients' => 'required|integer|min:1|max:128',
        ]);

        $server->update([
            'name' => $request->name,
            'max_clients' => $request->max_clients,
        ]);

        \App\Models\Log::create([
            'user_id' => $user->id,
            'action' => 'Updated Server General Settings',
            'target' => $server->name,
            'ip' => request()->ip()
        ]);

        return back()->with('success', 'Server general settings updated successfully.');
    }

    public function updateConnection(Request $request, Server $server)
    {
        $this->checkSettingsAccess($server);
        $user = auth()->user();

        $request->validate([
            'port' => 'required|integer|min:1024|max:65535',
            'port_gold' => 'nullable|integer|min:1024|max:65535',
        ]);

        $portError = $this->checkPortConflict($server->host_id, $request->port, $request->port_gold, $server->id);
        if ($portError) {
            return back()->with('error', $portError)->withInput();
        }

        $server->update([
            'port' => $request->port,
            'port_gold' => $request->port_gold,
        ]);

        $status = $this->serverService->getServerStatus($server);
        if ($status === 'Running') {
            try {
                $this->serverService->stopServer($server);
                sleep(1);
                $this->serverService->startServer($server);
                $server->update(['expected_state' => 'online']);
            } catch (\Exception $e) {
                return back()->with('error', 'Ports saved, but server failed to restart: ' . $e->getMessage());
            }
        }

        \App\Models\Log::create([
            'user_id' => $user->id,
            'action' => 'Changed Server Port & Restarted',
            'target' => $server->name,
            'ip' => request()->ip()
        ]);

        return back()->with('success', 'Connection info updated successfully.');
    }

    private function checkPortConflict($hostId, $port, $portGold, $excludeServerId = null)
    {
        if ($portGold && $port == $portGold) {
            return 'Server Port and Gold Port cannot be the same.';
        }

        $query = Server::where('host_id', $hostId);
        
        if ($excludeServerId) {
            $query->where('id', '!=', $excludeServerId);
        }

        $conflict = $query->where(function ($q) use ($port, $portGold) {
            $q->where('port', $port)->orWhere('port_gold', $port);
            if ($portGold) {
                $q->orWhere('port', $portGold)->orWhere('port_gold', $portGold);
            }
        })->first();

        if ($conflict) {
            return 'One of the specified ports is already in use by another server on this host.';
        }

        return null;
    }
}
