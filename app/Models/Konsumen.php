<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konsumen extends Model
{
    protected $guarded = ['id'];

    public function projek()
    {
        return $this->hasMany(Projek::class, 'id', 'Projek_Id');
    }

    public function prospek()
    {
        return $this->hasMany(Prospek::class, 'id', 'Prospek_Id');
    }
}
