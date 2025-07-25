<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $guarded = ['id'];

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class, 'konsumen_id');
    }

    public function properti()
    {
        return $this->belongsTo(Properti::class, 'properti_id');
    }

    public function blok()
    {
        return $this->belongsTo(Blok::class, 'blok_id');
    }

    public function tipe()
    {
        return $this->belongsTo(Tipe::class, 'tipe_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

}
