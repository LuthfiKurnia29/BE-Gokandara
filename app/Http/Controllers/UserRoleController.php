<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;

class UserRoleController extends Controller
{
    //
    public function getRoleByUserId(string $id)
    {
        $user = User::where('id', $id)->first();
        if($user != null){
            $userRole = UserRole::where('user_id', $user->id)->get();
            return response()->json([
                'status' => true,
                'message' => "Role of this user has successfully get",
                'data' => $userRole
            ]);
        }else{
            return response("Error to get data", 400);
        }
    }
}
