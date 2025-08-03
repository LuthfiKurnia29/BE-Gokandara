<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Properti extends Model
{
    protected $guarded = ['id'];

    public function projek()
    {
        return $this->belongsTo(Projek::class, 'project_id');
    }

    public function propertiGambar()
    {
        return $this->hasMany(Properti_Gambar::class, 'properti_id');
    }

    public function daftarHarga()
    {
        return $this->hasMany(DaftarHarga::class, 'properti_id');
    }
}
