<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $fillable = [
        'name',
        'host_id',
        'game_id',
        'port',
        'port_gold',
        'ftp_username',
        'ftp_password',
        'max_clients',
        'rcon_password',
        'start_script',
        'is_active',
        'auto_restart',
        'expected_state',
        'auto_backup',
        'backup_interval',
    ];

    public function host()
    {
        return $this->belongsTo(Host::class);
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot([
            'view_server',
            'start_server',
            'stop_server',
            'use_ftp',
            'view_ftp_credentials',
            'use_web_rcon',
            'view_rcon_password',
            'view_logs',
            'manage_server_users',
            'manage_settings'
        ])->withTimestamps();
    }

    public function backups()
    {
        return $this->hasMany(Backup::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
