<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Projek extends Model
{
    protected $guarded = ['id'];

    protected $with = ['gambars'];

    public function gambars()
    {
        return $this->hasMany(ProjekGambar::class);
    }
}
