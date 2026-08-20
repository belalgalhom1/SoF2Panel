<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $fillable = [
        'server_id',
        'filename',
        'size',
    ];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
}
