<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    //
    protected $guarded = ['id'];
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function menuAccesses()
    {
        return $this->hasMany(UserMenuAccess::class, 'user_role_id', 'id');
    }
}
