<?php

namespace App\Services;

use App\Models\Host;
use App\Models\Server;
use App\Models\Game;
use phpseclib3\Net\SSH2;
use phpseclib3\Net\SFTP;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class ServerService
{
    private function escapeLinuxArg($string)
    {
        return "'" . str_replace("'", "'\\''", $string) . "'";
    }

    public function getSSH(Host $host)
    {
        $ssh = new SSH2($host->hostname, $host->port, 10);
        
        $password = $host->password;
        try {
            $password = Crypt::decryptString($host->password);
        } catch (\Exception $e) {
            $password = $host->password;
        }

        if (!$ssh->login($host->username, $password)) {
            throw new \Exception("SSH Authentication Failed to Host: " . $host->name);
        }
        return $ssh;
    }

    public function provisionServer(Server $server, string $password)
    {
        $host = $server->host;
        $game = $server->game;
        $username = $this->escapeLinuxArg($server->ftp_username);
        $baseLoc = $this->escapeLinuxArg($game->base_location);
        $homeDir = $this->escapeLinuxArg("/home/{$server->ftp_username}");
        $passwordEscaped = $this->escapeLinuxArg($server->ftp_username . ':' . $password);

        $ssh = $this->getSSH($host);

        $output = $ssh->exec("useradd -m -d {$homeDir} -s /bin/bash {$username}");
        if ($ssh->getExitStatus() !== 0) throw new \Exception("Failed to create user: " . $output);

        $output = $ssh->exec("echo {$passwordEscaped} | chpasswd");
        if ($ssh->getExitStatus() !== 0) throw new \Exception("Failed to set password: " . $output);

        $output = $ssh->exec("cp -R {$baseLoc}/* {$homeDir}/");
        if ($ssh->getExitStatus() !== 0) throw new \Exception("Failed to copy game files. Make sure the base location exists on the host! Output: " . $output);

        $ssh->exec("chown -R {$username}:{$username} {$homeDir}/");
    }

    public function startServer(Server $server)
    {
        $host = $server->host;
        $ssh = $this->getSSH($host);
        
        $username = $server->ftp_username;
        $screenName = str_replace(' ', '_', $server->name);
        $homeDir = "/home/{$username}";
        
        $rconPass = $server->rcon_password;
        try {
            $rconPass = Crypt::decryptString($server->rcon_password);
        } catch (\Exception $e) {
            $rconPass = $server->rcon_password;
        }

        $clean_rcon = $this->escapeLinuxArg($rconPass);
        $clean_name = $this->escapeLinuxArg($server->name);

        $base_script = $server->start_script ?: $server->game->start_script;

        $script = str_replace(
            ['{server_port}', '{port}', '{server_port_gold}', '{max_clients}', '{clients}', '{rconpassword}', '{rcon_password}', '{server_account}', '{server_name}'],
            [$server->port, $server->port, $server->port_gold ?? $server->port, $server->max_clients, $server->max_clients, $clean_rcon, $clean_rcon, $username, $clean_name],
            $base_script
        );

        $clean_user = $this->escapeLinuxArg($username);
        $clean_home = $this->escapeLinuxArg($homeDir);
        $clean_screen = $this->escapeLinuxArg($screenName);
        
        $b64Script = base64_encode($script);
        $escapedB64 = $this->escapeLinuxArg($b64Script);

        $innerBashCommand = "cd {$clean_home} && rm -f screenlog.0 && echo {$escapedB64} | base64 -d > .server_start.sh && screen -L -dmS {$clean_screen} bash .server_start.sh";
        $command = "su - {$clean_user} -c " . $this->escapeLinuxArg($innerBashCommand);
        $output = $ssh->exec($command);
        
        if ($ssh->getExitStatus() !== 0) {
            throw new \Exception("Command failed to execute: " . $output);
        }

        usleep(1500000);

        if ($this->getServerStatus($server) !== 'Running') {
            $logOutput = trim($ssh->exec("su - {$clean_user} -c " . $this->escapeLinuxArg("cat {$clean_home}/screenlog.0 2>/dev/null")));
            if (empty($logOutput)) {
                $logOutput = "(No terminal output captured. This usually means the executable is completely missing, has a syntax error, or permission was denied.)";
            }
            throw new \Exception("Terminal Error: " . $logOutput);
        }
    }

    public function stopServer(Server $server)
    {
        $host = $server->host;
        $ssh = $this->getSSH($host);
        
        $escapedUsername = $this->escapeLinuxArg($server->ftp_username);
        $command = "su - {$escapedUsername} -c 'pkill screen'";
        $ssh->exec($command);
    }

    public function createBackup(Server $server)
    {
        $host = $server->host;
        $ssh = $this->getSSH($host);
        
        $username = $this->escapeLinuxArg($server->ftp_username);
        $cleanUsername = $server->ftp_username;
        $backupDir = "/home/{$cleanUsername}/backups";
        
        $ssh->exec("su - {$username} -c 'mkdir -p {$backupDir}'");

        $latestBackup = $server->backups()->orderBy('created_at', 'desc')->first();
        $linkDest = '';
        if ($latestBackup) {
            $linkDest = "--link-dest={$backupDir}/{$latestBackup->filename}/";
        }

        $newFolderName = "backup_" . date('Ymd_His');
        
        $ssh->setTimeout(0);
        
        $command = "su - {$username} -c 'rsync -a --delete --exclude=\"backups\" --exclude=\".*\" {$linkDest} /home/{$cleanUsername}/ {$backupDir}/{$newFolderName}/'";
        $ssh->exec($command);
        
        $sizeOutput = trim($ssh->exec("su - {$username} -c 'du -sm {$backupDir}/{$newFolderName} | cut -f1'"));
        $sizeMb = intval($sizeOutput);

        $server->backups()->create([
            'filename' => $newFolderName,
            'size' => $sizeMb * 1024 * 1024,
        ]);
        
        $backupLimit = \App\Models\Setting::get('backup_limit', 5);
        $totalBackups = $server->backups()->count();
        
        if ($totalBackups > $backupLimit) {
            $toDelete = $totalBackups - $backupLimit;
            $oldestBackups = $server->backups()->orderBy('created_at', 'asc')->limit($toDelete)->get();
            
            foreach ($oldestBackups as $oldBackup) {
                try {
                    $this->deleteBackup($server, $oldBackup);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to prune old backup {$oldBackup->filename}: " . $e->getMessage());
                }
            }
        }
        
        return true;
    }

    public function restoreBackup(Server $server, \App\Models\Backup $backup)
    {
        $host = $server->host;
        $ssh = $this->getSSH($host);
        
        $username = $this->escapeLinuxArg($server->ftp_username);
        $cleanUsername = $server->ftp_username;
        $backupDir = "/home/{$cleanUsername}/backups/{$backup->filename}";

        if ($this->getServerStatus($server) === 'Running') {
            $this->stopServer($server);
            sleep(1);
        }

        $ssh->setTimeout(0);

        $command = "su - {$username} -c 'rsync -a --delete --exclude=\"backups\" --exclude=\".*\" {$backupDir}/ /home/{$cleanUsername}/'";
        $ssh->exec($command);

        return true;
    }

    public function getSFTP(Host $host): SFTP
    {
        $password = $host->password;
        try {
            $password = Crypt::decryptString($host->password);
        } catch (\Exception $e) {
            $password = $host->password;
        }

        $sftp = new SFTP($host->hostname, $host->port, 10);
        if (!$sftp->login($host->username, $password)) {
            throw new \Exception("SFTP Authentication Failed to Host: " . $host->name);
        }
        return $sftp;
    }

    /**
     * Build (once, then cache) a fast-compressed tar of the backup folder.
     * Returns [remoteTarPath, sizeBytes].
     */
    public function prepareBackupArchive(Server $server, \App\Models\Backup $backup): array
    {
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $backup->filename)) {
            throw new \Exception("Invalid backup filename.");
        }

        @set_time_limit(0);

        $host = $server->host;
        $ssh = $this->getSSH($host);
        $ssh->setTimeout(0);

        $username = $this->escapeLinuxArg($server->ftp_username);
        $cleanUsername = $server->ftp_username;
        $backupsDir = "/home/{$cleanUsername}/backups";
        $backupPath = "{$backupsDir}/{$backup->filename}";
        // Cached archive lives next to the backup folder so repeat downloads are instant.
        $remoteTar = "{$backupsDir}/{$backup->filename}.tar.gz";

        $check = trim($ssh->exec("test -d " . escapeshellarg($backupPath) . " && echo OK || echo MISSING"));
        if ($check !== 'OK') {
            throw new \Exception("Backup folder not found on host.");
        }

        $exists = trim($ssh->exec("test -f " . escapeshellarg($remoteTar) . " && echo OK || echo MISSING"));
        if ($exists !== 'OK') {
            // GZIP=-1 = fastest gzip level (~3-5x faster than default -6, slightly bigger file).
            // Run as the ftp user so file ownership stays correct.
            $inner = "GZIP=-1 tar -czf " . escapeshellarg($remoteTar) .
                " -C " . escapeshellarg($backupsDir) .
                " " . escapeshellarg($backup->filename);
            $ssh->exec("su - {$username} -c " . escapeshellarg($inner));

            if ($ssh->getExitStatus() !== 0) {
                $ssh->exec("rm -f " . escapeshellarg($remoteTar));
                throw new \Exception("Failed to archive backup for download.");
            }
        }

        $sftp = $this->getSFTP($host);
        $stat = $sftp->stat($remoteTar);
        if ($stat === false || empty($stat['size'])) {
            throw new \Exception("Backup archive not found or empty.");
        }

        return [$remoteTar, (int) $stat['size']];
    }

    /**
     * Stream the cached archive straight host -> browser, chunk by chunk.
     * No temp file on the panel, low memory, download starts immediately.
     */
    public function streamBackupDownload(Server $server, \App\Models\Backup $backup, callable $emit): int
    {
        [$remoteTar, $size] = $this->prepareBackupArchive($server, $backup);

        $sftp = $this->getSFTP($server->host);

        $ok = $sftp->get($remoteTar, $emit);
        if ($ok === false) {
            throw new \Exception("Failed to transfer backup archive from host.");
        }

        return $size;
    }

    public function deleteBackup(Server $server, \App\Models\Backup $backup)
    {
        $host = $server->host;
        $ssh = $this->getSSH($host);
        
        $username = $this->escapeLinuxArg($server->ftp_username);
        $cleanUsername = $server->ftp_username;
        
        $command = "su - {$username} -c 'rm -rf /home/{$cleanUsername}/backups/{$backup->filename} /home/{$cleanUsername}/backups/{$backup->filename}.tar.gz'";
        $ssh->exec($command);

        $backup->delete();
        
        return true;
    }

    public function destroyServer(Server $server)
    {
        try {
            $host = $server->host;
            $ssh = $this->getSSH($host);
            
            $username = $this->escapeLinuxArg($server->ftp_username);
            $screenName = $this->escapeLinuxArg(str_replace(' ', '_', $server->name));

            $ssh->exec("su - {$username} -c " . $this->escapeLinuxArg("screen -S {$screenName} -X quit"));
            
            usleep(500000); 

            $ssh->exec("pkill -u {$username}");

            $ssh->exec("userdel -r {$username}");
        } catch (\Exception $e) {
        }
    }

    public function getServerStatus(Server $server)
    {
        try {
            $host = $server->host;
            $ssh = $this->getSSH($host);
            $username = $this->escapeLinuxArg($server->ftp_username);

            $output = $ssh->exec("su - {$username} -c 'screen -ls'");
            return (strpos($output, '(Detached)') !== false || strpos($output, '(Attached)') !== false) ? 'Running' : 'Stopped';
        } catch (\Exception $e) {
            return 'Error';
        }
    }

    public function pingServer(Server $server)
    {
        try {
            $host = $server->host->hostname;
            $port = $server->port;
            
            $socket = @fsockopen("udp://$host", $port, $errno, $errstr, 2);
            if (!$socket) {
                return false;
            }

            stream_set_timeout($socket, 1);
            
            $packet = "\xff\xff\xff\xffgetinfo";
            fwrite($socket, $packet);
            
            $response = fread($socket, 4096);
            fclose($socket);
            
            if (strpos($response, 'infoResponse') !== false || strpos($response, 'statusResponse') !== false) {
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
