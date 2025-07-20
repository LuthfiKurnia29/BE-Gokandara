<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserMenuAccess;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function me(Request $request){
        $user = Auth::user(); // Ambil user dari access token
        // var_dump($user); die;
        // Ambil semua role user
        $roles = UserRole::with('role') // join ke tabel role jika ada
                    ->where('user_id', $user->id)
                    ->get();

        $roleIds = $roles->pluck('role_id');

        // Ambil akses menu berdasarkan role
        $menuAccesses = UserMenuAccess::with('menu')->whereIn('user_role_id', $roleIds)->get();

        return response()->json([
            'user' => $user,
            'roles' => $roles,
            'access' => $menuAccesses
        ]);
    }

    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;
        $data = User::where(function ($query) use ($search) {
                if ($search) {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                }
            })
            ->orderBy('id', 'desc')
            ->paginate($per);

        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:konsumens',
            'role_id' => 'required'
            // 'password' => 'required|string|max:15',
            // 'kesiapan_dana' => 'required|numeric|min:0',
            // 'pengalaman' => 'required|string|max:255',
        ]);
        if($request['password']){
            $hashedPass = Hash::make($request['password']);
            $validate['password'] = $hashedPass;
        }

        $user = User::create($validate);
        if($request['role_id']){
            UserRole::create([
                'user_id' => $user->id,
                'role_id' => $request['role_id'],
                'is_allowed' => true,
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
