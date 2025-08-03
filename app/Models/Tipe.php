<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tipe extends Model
{
    protected $guarded = ['id'];

    public function daftarHarga()
    {
        return $this->hasMany(DaftarHarga::class, 'tipe_id');
    }
}
