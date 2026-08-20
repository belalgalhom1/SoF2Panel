<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Server;
use App\Models\User;

class ServerUserController extends Controller
{
    private function checkPermission(Server $server)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $pivot = $server->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !$pivot->manage_server_users) {
                abort(403);
            }
        }
    }

    public function index(Server $server)
    {
        $this->checkPermission($server);

        $serverUsers = $server->users()->get();
        
        $allUsers = User::whereNotIn('role', ['admin', 'owner'])->get();
        
        $availableUsers = $allUsers->reject(function ($user) use ($serverUsers) {
            return $serverUsers->contains('id', $user->id);
        });

        $globalAdmins = User::whereIn('role', ['admin', 'owner'])->get();

        return view('servers.users', compact('server', 'serverUsers', 'availableUsers', 'globalAdmins'));
    }

    public function store(Request $request, Server $server)
    {
        $this->checkPermission($server);

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $permissions = [
            'view_server' => $request->has('view_server'),
            'start_server' => $request->has('start_server'),
            'stop_server' => $request->has('stop_server'),
            'use_ftp' => $request->has('use_ftp'),
            'view_ftp_credentials' => $request->has('view_ftp_credentials'),
            'use_web_rcon' => $request->has('use_web_rcon'),
            'view_rcon_password' => $request->has('view_rcon_password'),
            'view_logs' => $request->has('view_logs'),
            'manage_server_users' => $request->has('manage_server_users'),
            'manage_settings' => $request->has('manage_settings'),
        ];

        $server->users()->syncWithoutDetaching([
            $request->user_id => $permissions
        ]);
        
        \App\Models\Log::create([
            'user_id' => auth()->user()->id,
            'action' => 'Assigned User to Server',
            'target' => "User ID: {$request->user_id}, Server: {$server->name}",
            'ip' => request()->ip()
        ]);

        return back()->with('success', 'User assigned successfully.');
    }

    public function update(Request $request, Server $server, $userId)
    {
        $this->checkPermission($server);

        $permissions = [
            'view_server' => $request->has('view_server'),
            'start_server' => $request->has('start_server'),
            'stop_server' => $request->has('stop_server'),
            'use_ftp' => $request->has('use_ftp'),
            'view_ftp_credentials' => $request->has('view_ftp_credentials'),
            'use_web_rcon' => $request->has('use_web_rcon'),
            'view_rcon_password' => $request->has('view_rcon_password'),
            'view_logs' => $request->has('view_logs'),
            'manage_server_users' => $request->has('manage_server_users'),
            'manage_settings' => $request->has('manage_settings'),
        ];

        $server->users()->updateExistingPivot($userId, $permissions);

        \App\Models\Log::create([
            'user_id' => auth()->user()->id,
            'action' => 'Updated Server User Permissions',
            'target' => "User ID: {$userId}, Server: {$server->name}",
            'ip' => request()->ip()
        ]);

        return back()->with('success', 'User permissions updated.');
    }

    public function destroy(Server $server, $userId)
    {
        $this->checkPermission($server);

        $server->users()->detach($userId);

        \App\Models\Log::create([
            'user_id' => auth()->user()->id,
            'action' => 'Removed User from Server',
            'target' => "User ID: {$userId}, Server: {$server->name}",
            'ip' => request()->ip()
        ]);

        return back()->with('success', 'User removed from server.');
    }
}
