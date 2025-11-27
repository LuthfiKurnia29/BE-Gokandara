<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiDetailPembayaran extends Model {
    protected $fillable = ['transaksi_id', 'skema_pembayaran_id', 'detail_skema_pembayaran_id', 'tanggal', 'nama', 'persentase'];

    public function transaksi() {
        return $this->belongsTo(Transaksi::class);
    }

    public function skemaPembayaran() {
        return $this->belongsTo(SkemaPembayaran::class);
    }


    public function detailSkemaPembayaran() {
        return $this->belongsTo(DetailSkemaPembayaran::class);
    }
}
