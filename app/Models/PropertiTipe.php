<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PropertiTipe extends Model {
    protected $guarded = ['id'];

    public function properti() {
        return $this->belongsTo(Properti::class, 'properti_id');
    }

    public function tipe() {
        return $this->belongsTo(Tipe::class, 'tipe_id');
    }
}
