<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Properti extends Model {
    protected $guarded = ['id'];

    protected $appends = ['unit_ids', 'tipe_ids', 'blok_ids'];
    public function getUnitIdsAttribute() {
        return $this->units->pluck('id');
    }

    public function getTipeIdsAttribute() {
        return $this->tipes->pluck('id');
    }

    public function getBlokIdsAttribute() {
        return $this->bloks->pluck('id');
    }

    public function projek() {
        return $this->belongsTo(Projek::class, 'project_id');
    }

    public function propertiGambar() {
        return $this->hasMany(Properti_Gambar::class, 'properti_id');
    }

    public function fasilitas() {
        return $this->hasMany(Fasilitas::class, 'properti_id');
    }

    public function daftarHarga() {
        return $this->hasMany(DaftarHarga::class, 'properti_id');
    }

    public function units() {
        return $this->belongsToMany(Unit::class, 'properti_units', 'properti_id', 'unit_id');
    }

    public function tipes() {
        return $this->belongsToMany(Tipe::class, 'properti_tipes', 'properti_id', 'tipe_id');
    }

    public function blok() {
        return $this->belongsToMany(Blok::class, 'properti_bloks', 'properti_id', 'blok_id');
    }
}
