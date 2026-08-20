<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Server;
use Illuminate\Support\Facades\Crypt;
use phpseclib3\Net\SSH2;

class ServerPasswordController extends Controller
{
    public function updateFtp(Request $request, Server $server)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $pivot = $server->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !$pivot->manage_server_users) {
                abort(403);
            }
        }

        $request->validate(['password' => 'required|string|min:6']);
        $newPassword = $request->password;

        $host = $server->host;
        $ssh = new SSH2($host->hostname, $host->port);
        if (!$ssh->login($host->username, Crypt::decryptString($host->password))) {
            return back()->with('error', 'Authentication to host failed.');
        }

        $username = escapeshellarg($server->ftp_username);
        $passwordEscaped = escapeshellarg($server->ftp_username . ':' . $newPassword);

        $ssh->exec("echo {$passwordEscaped} | chpasswd");

        $server->update([
            'ftp_password' => Crypt::encryptString($newPassword)
        ]);

        \App\Models\Log::create([
            'user_id' => $user->id,
            'action' => 'Changed FTP Password',
            'target' => $server->name,
            'ip' => request()->ip()
        ]);

        return back()->with('success', 'FTP Password updated successfully.');
    }

    public function updateRcon(Request $request, Server $server)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            $pivot = $server->users()->where('user_id', $user->id)->first()?->pivot;
            if (!$pivot || !$pivot->manage_server_users) {
                abort(403);
            }
        }

        $request->validate(['rcon_password' => 'required|string|min:3']);
        $newPassword = $request->rcon_password;

        $serverService = app(\App\Services\ServerService::class);
        $status = $serverService->getServerStatus($server);

        if ($status === 'Running') {
            try {
                $oldPassword = Crypt::decryptString($server->rcon_password);
            } catch (\Throwable $e) {
                $oldPassword = $server->rcon_password;
            }

            $host = $server->host->hostname;
            $port = $server->port;

            $rconController = app(\App\Http\Controllers\WebRconController::class);

            $rconController->sendRconCommand($host, $port, $oldPassword, "set rconpassword \"$newPassword\"");

            usleep(500000);

            $verifyResponse = $rconController->sendRconCommand($host, $port, $newPassword, "rconpassword");

            if (str_contains($verifyResponse, 'Error') || empty(trim($verifyResponse))) {
                return back()->with('error', 'Failed to verify the new RCON password on the server. Old password is still active.');
            }

            $server->update(['rcon_password' => Crypt::encryptString($newPassword)]);

            \App\Models\Log::create([
                'user_id' => $user->id,
                'action' => 'Changed RCON Password',
                'target' => $server->name,
                'ip' => request()->ip()
            ]);

            return back()->with('success', 'RCON password changed successfully!');
        } else {
            $server->update(['rcon_password' => Crypt::encryptString($newPassword)]);

            \App\Models\Log::create([
                'user_id' => $user->id,
                'action' => 'Changed RCON Password',
                'target' => $server->name,
                'ip' => request()->ip()
            ]);

            return back()->with('success', 'RCON password changed successfully!');
        }
    }
}
