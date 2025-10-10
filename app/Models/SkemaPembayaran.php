<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkemaPembayaran extends Model {
    protected $guarded = ['id'];

    public function details() {
        return $this->hasMany(DetailSkemaPembayaran::class);
    }
}
