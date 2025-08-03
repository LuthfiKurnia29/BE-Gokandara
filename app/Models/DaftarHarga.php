<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DaftarHarga extends Model
{
    protected $guarded = ["id"];

    public function properti()
    {
        return $this->belongsTo(Properti::class, 'properti_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function tipe()
    {
        return $this->belongsTo(Tipe::class, 'tipe_id');
    }
}
