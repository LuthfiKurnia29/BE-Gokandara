<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chatting extends Model
{
    // use SoftDeletes;

    protected $guarded = ['id'];

    public function pengirim()
    {
        return $this->belongsTo(User::class, 'user_pengirim_id');
    }

    public function penerima()
    {
        return $this->belongsTo(User::class, 'user_penerima_id');
    }
}
