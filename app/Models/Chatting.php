<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chatting extends Model {
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $appends = ['file_url'];

    public function getFileUrlAttribute() {
        if (!$this->file) {
            return null;
        }
        return asset('storage/' . $this->file);
    }

    public function pengirim() {
        return $this->belongsTo(User::class, 'user_pengirim_id');
    }

    public function penerima() {
        return $this->belongsTo(User::class, 'user_penerima_id');
    }

    public function notifikasi() {
        return $this->hasMany(Notifikasi::class, 'chat_id');
    }
}
