<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konsumen extends Model
{
    protected $guarded = ['id'];

    // public function projek()
    // {
    //     return $this->belongsTo(Projek::class, 'Projek_Id');
    // }

    // public function prospek()
    // {
    //     return $this->belongsTo(Prospek::class, 'Prospek_Id');
    // }
}
