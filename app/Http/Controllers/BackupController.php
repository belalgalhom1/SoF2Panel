<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Server;
use App\Models\Backup;
use App\Services\ServerService;

class BackupController extends Controller
{
    protected $serverService;

    public function __construct(ServerService $serverService)
    {
        $this->serverService = $serverService;
    }

    protected function checkAccess(Server $server)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $pivot = $server->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !$pivot->manage_settings) {
                abort(403, 'You do not have permission to manage backups.');
            }
        }
    }

    public function index(Server $server)
    {
        $this->checkAccess($server);
        $backups = $server->backups()->orderBy('created_at', 'desc')->get();
        return view('servers.backups', compact('server', 'backups'));
    }

    public function store(Request $request, Server $server)
    {
        $this->checkAccess($server);
        
        $this->serverService->createBackup($server);

        \App\Models\Log::create([
            'user_id' => auth()->user()->id,
            'action' => 'Created Backup',
            'target' => "Backup created for {$server->name}",
            'ip' => request()->ip()
        ]);

        return back()->with('success', 'Backup created successfully!');
    }

    public function restore(Request $request, Server $server, Backup $backup)
    {
        $this->checkAccess($server);

        if ($backup->server_id !== $server->id) {
            abort(403);
        }

        $this->serverService->restoreBackup($server, $backup);

        \App\Models\Log::create([
            'user_id' => auth()->user()->id,
            'action' => 'Restored Backup',
            'target' => "Restored {$server->name} to backup {$backup->filename}",
            'ip' => request()->ip()
        ]);

        return back()->with('success', 'Server successfully restored from backup! The server was stopped during restoration, you may start it now.');
    }

    public function destroy(Server $server, Backup $backup)
    {
        $this->checkAccess($server);

        if ($backup->server_id !== $server->id) {
            abort(403);
        }

        $this->serverService->deleteBackup($server, $backup);

        \App\Models\Log::create([
            'user_id' => auth()->user()->id,
            'action' => 'Deleted Backup',
            'target' => "Deleted backup {$backup->filename} for {$server->name}",
            'ip' => request()->ip()
        ]);

        return back()->with('success', 'Backup deleted successfully.');
    }
}
