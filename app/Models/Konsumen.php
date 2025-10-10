<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Konsumen extends Model {
    use SoftDeletes;

    protected $guarded = ['id'];
    protected $appends = ['gambar_url'];

    public function getGambarUrlAttribute() {
        return asset('files/' . $this->gambar);
    }

    public function createdBy() {
        return $this->belongsTo(User::class, 'created_id');
    }

    public function projek() {
        return $this->belongsTo(Projek::class, 'projek_id');
    }

    public function prospek() {
        return $this->belongsTo(Prospek::class, 'prospek_id');
    }

    public function followups() {
        return $this->hasMany(FollowupMonitoring::class);
    }

    public function transaksi() {
        return $this->hasMany(Transaksi::class);
    }

    public function latestTransaksi() {
        return $this->hasOne(Transaksi::class)->latest();
    }
}
