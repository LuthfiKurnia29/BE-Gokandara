<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Target extends Model {
    protected $guarded = ['id'];

    public function role() {
        return $this->belongsTo(Role::class);
    }
}
