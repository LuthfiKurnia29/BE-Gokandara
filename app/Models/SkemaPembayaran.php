<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SkemaPembayaran extends Model {
    protected $guarded = ['id'];
    protected $with = ['details'];

    public function details() {
        return $this->hasMany(DetailSkemaPembayaran::class);
    }
}
