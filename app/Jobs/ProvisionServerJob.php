<?php

namespace App\Jobs;

use App\Models\Server;
use App\Services\ServerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProvisionServerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $server;
    public $password;

    public function __construct(Server $server, string $password)
    {
        $this->server = $server;
        $this->password = $password;
    }

    public function handle(ServerService $serverService): void
    {
        try {
            $serverService->provisionServer($this->server, $this->password);
            $this->server->update(['is_active' => true]);
        } catch (\Throwable $e) {
            $this->failed($e);
        }
    }

    public function failed(\Throwable $exception)
    {
        $this->server->update(['is_active' => false]);
        \Log::error("Provisioning failed for Server ID {$this->server->id}: " . $exception->getMessage());
    }
}
