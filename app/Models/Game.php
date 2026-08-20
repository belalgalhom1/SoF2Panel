<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $fillable = [
        'name',
        'base_location',
        'start_script',
    ];

    public function servers()
    {
        return $this->hasMany(Server::class);
    }
}
