<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['username', 'email', 'password', 'role', 'status', 'is_external'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
            'is_external' => 'boolean',
        ];
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isOwner()
    {
        return $this->role === 'owner';
    }

    public function servers()
    {
        return $this->belongsToMany(Server::class)->withPivot([
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

    public function apiKeys()
    {
        return $this->hasMany(ApiKey::class);
    }
}
