<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjekGambar extends Model {
    protected $guarded = ['id'];
    protected $appends = ['gambar_url'];

    public function getGambarUrlAttribute() {
        return asset('files/' . $this->gambar);
    }

    public function projek() {
        return $this->belongsTo(Projek::class);
    }
}
