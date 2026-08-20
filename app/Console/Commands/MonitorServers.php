<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Server;
use App\Services\ServerService;

class MonitorServers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'servers:monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor active servers and automatically restart them if they crashed.';

    /**
     * Execute the console command.
     */
    public function handle(ServerService $serverService)
    {
        $servers = Server::where('is_active', true)
            ->where('auto_restart', true)
            ->where('expected_state', 'online')
            ->get();

        foreach ($servers as $server) {
            $status = $serverService->getServerStatus($server);
            
            $needsRestart = false;

            if ($status === 'Stopped') {
                $needsRestart = true;
            } else if ($status === 'Running') {
                if (!$serverService->pingServer($server)) {
                    sleep(5);
                    if (!$serverService->pingServer($server)) {
                        $needsRestart = true;

                        try {
                            $serverService->stopServer($server);
                            sleep(1);
                        } catch (\Exception $e) {}
                    }
                }
            }

            if ($needsRestart) {
                try {
                    $serverService->startServer($server);
                    
                    \App\Models\Log::create([
                        'user_id' => null,
                        'action' => 'Auto-Restart Server (Cron)',
                        'target' => $server->name,
                        'ip' => '127.0.0.1'
                    ]);

                    $this->info("Server {$server->name} auto-restarted!");
                } catch (\Exception $e) {
                    $this->error("Failed to auto-restart server {$server->name}: " . $e->getMessage());
                }
            }
        }
    }
}
