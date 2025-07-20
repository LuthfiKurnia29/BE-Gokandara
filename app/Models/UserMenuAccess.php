<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMenuAccess extends Model
{
    //
    protected $guarded = ['id'];
    public function menu(){
        return $this->belongsTo(Menu::class, 'menu_id');
    }
    public function user_role(){
        return $this->belongsTo(UserRole::class, 'user_role_id');
    }
}
