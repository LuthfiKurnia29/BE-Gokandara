<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailSkemaPembayaran extends Model {
    protected $fillable = ['skema_pembayaran_id', 'nama', 'persentase'];

    public function skemaPembayaran() {
        return $this->belongsTo(SkemaPembayaran::class);
    }
}
