<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $guarded = ['id'];

    public function daftarHarga()
    {
        return $this->hasMany(DaftarHarga::class, 'unit_id');
    }
}
