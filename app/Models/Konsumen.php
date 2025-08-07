<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konsumen extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['gambar_url'];

    public function getGambarUrlAttribute() {
        return asset('files/' . $this->gambar);
    }

    public function projek()
    {
        return $this->belongsTo(Projek::class, 'projek_id');
    }

    public function prospek()
    {
        return $this->belongsTo(Prospek::class, 'prospek_id');
    }
}
