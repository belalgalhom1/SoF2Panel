<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunBackups extends Command
{
    protected $signature = 'servers:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run scheduled server backups based on their intervals';

    /**
     * Execute the console command.
     */
    public function handle(\App\Services\ServerService $serverService)
    {
        $servers = \App\Models\Server::where('auto_backup', true)->get();

        foreach ($servers as $server) {
            $latestBackup = $server->backups()->orderBy('created_at', 'desc')->first();
            
            $shouldRun = false;
            if (!$latestBackup) {
                $shouldRun = true;
            } else {
                $minutesSinceLast = $latestBackup->created_at->diffInMinutes(now());
                if ($minutesSinceLast >= $server->backup_interval) {
                    $shouldRun = true;
                }
            }

            if ($shouldRun) {
                $this->info("Running backup for server ID: {$server->id}");
                try {
                    $serverService->createBackup($server);
                    $this->info("Successfully backed up server {$server->name}");
                } catch (\Exception $e) {
                    $this->error("Failed to backup server {$server->name}: " . $e->getMessage());
                }
            }
        }
    }
}
