<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['user_id', 'server_id', 'subject', 'category', 'status'])]
class Ticket extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }
}
