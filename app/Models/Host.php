<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Host extends Model
{
    protected $fillable = [
        'name',
        'hostname',
        'port',
        'username',
        'password',
    ];

    public function servers()
    {
        return $this->hasMany(Server::class);
    }
}
