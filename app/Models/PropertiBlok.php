<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertiBlok extends Model {
    protected $guarded = ['id'];

    public function properti() {
        return $this->belongsTo(Properti::class, 'properti_id');
    }

    public function blok() {
        return $this->belongsTo(Blok::class, 'blok_id');
    }
}
