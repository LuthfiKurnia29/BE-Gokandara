<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowupMonitoring extends Model
{
    protected $guarded = ['id'];

    public function konsumen() {
        return $this->belongsTo(Konsumen::class);
    }

    public function prospek() {
        return $this->belongsTo(Prospek::class);
    }

    public function sales() {
        return $this->belongsTo(User::class, 'sales_id');
    }
}
