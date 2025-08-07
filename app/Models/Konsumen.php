<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Konsumen extends Model
{
    protected $guarded = ['id'];
    protected $appends = ['image_url'];

    public function getImageUrlAttribute() {
        return asset('files/' . $this->image);
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
