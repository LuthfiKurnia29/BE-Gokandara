<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Projek extends Model
{
    protected $guarded = ['id'];

    public function gambar()
    {
        return $this->hasMany(ProjekGambar::class);
    }
}
