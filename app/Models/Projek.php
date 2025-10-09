<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Projek extends Model {
    protected $guarded = ['id'];

    protected $with = ['gambars'];
    protected $appends = ['logo_url'];

    public function getLogoUrlAttribute() {
        return asset('files/' . $this->logo);
    }

    public function gambars() {
        return $this->hasMany(ProjekGambar::class);
    }
}
