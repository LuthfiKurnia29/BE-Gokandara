<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranProjeks extends Model {
    protected $guarded = ['id'];

    public function projek() {
        return $this->belongsTo(Projek::class);
    }

    public function tipe() {
        return $this->belongsTo(Tipe::class);
    }

    public function skemaPembayaran() {
        return $this->belongsTo(SkemaPembayaran::class);
    }
}
