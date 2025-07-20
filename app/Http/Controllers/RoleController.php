<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    //
    public function getAllRole(){
        $roles = Role::get();
        return response()->json([
            'success' => true,
            'message' => 'Successfully get all roles',
            'data' => $roles
        ]);
    }
}
