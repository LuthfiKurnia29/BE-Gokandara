<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $guarded = ['id'];

    protected $attributes = [
        'chat_id' => null,
        'konsumen_id' => null,
        'is_read' => false,
    ];

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class, 'konsumen_id');
    }

    public function chatting()
    {
        return $this->belongsTo(Chatting::class, 'chat_id');
    }
}
