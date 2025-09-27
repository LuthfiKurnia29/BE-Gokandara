<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjekGambar extends Model
{
    protected $guarded = ['id'];

    public function projek()
    {
        return $this->belongsTo(Projek::class);
    }
}
