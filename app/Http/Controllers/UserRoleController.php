<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserMenuAccess;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserRoleController extends Controller
{
    //
    public function getRoleByUserId()
    {
        $user = Auth::user();
        if($user != null){
            $userRole = UserRole::where('user_id', $user->id)->first();
            $akses = UserMenuAccess::where('user_role_id', $userRole->role_id)->get();
            return response()->json([
                'status' => true,
                'message' => "Role of this user has successfully get",
                'data' => $userRole,
                'accessMenu' => $akses
            ]);
        }else{
            return response("Error to get data", 400);
        }
    }
}
