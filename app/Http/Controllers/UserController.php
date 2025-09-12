<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\UserMenuAccess;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller {
    /**
     * Display a listing of the resource.
     */

    public function me(Request $request) {
        $user = Auth::user(); // Ambil user dari access token
        // Ambil semua role user
        $roles = UserRole::with('role')->where('user_id', $user->id)->get();

        $userRoleIds = $roles->pluck('id');
        $roleIds = $roles->pluck('role_id');

        // Ambil akses menu berdasarkan role
        $menuAccesses = UserMenuAccess::with('menu')->whereIn('user_role_id', $userRoleIds)->get();

        return response()->json([
            'user' => $user,
            'roles' => $roles,
            'access' => $menuAccesses
        ]);
    }

    public function index(Request $request) {
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;
        $data = User::with('roles.role')->where(function ($query) use ($search) {
            if ($search) {
                $query->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            }
        })
            ->when(isset($request->roles), function ($query) use ($request) {
                $query->whereHas('roles.role', function ($query) use ($request) {
                    $query->whereIn('name', $request->roles);
                });
            })
            ->when(auth()->user()->hasRole('Supervisor'), function ($query) {
                $query->where('parent_id', auth()->user()->id);
            })
            ->when(auth()->user()->hasRole('Mitra'), function ($query) {
                $query->whereHas('roles.role', function ($query) use ($request) {
                    $query->where('name', 'Admin');
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($per);

        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $user = Auth::user();
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role_id' => 'required',
            'nip' => 'required'
            // 'parent_id' => $user->id
            // 'password' => 'required|string|max:15',
            // 'kesiapan_dana' => 'required|numeric|min:0',
            // 'pengalaman' => 'required|string|max:255',
        ], [
            'email.unique' => 'Email yang diinput sudah ada.'
        ]);

        $validate['parent_id'] = $request['parent_id'];
        if ($request['password']) {
            $hashedPass = Hash::make($request['password']);
            $validate['password'] = $hashedPass;

            $encPass = Crypt::encryptString($request['password']);
            $validate['enc_pw'] = $encPass;
        }

        $user = User::create($validate);
        if ($request['role_id']) {
            $userRole = UserRole::create([
                'user_id' => $user->id,
                'role_id' => $request['role_id'],
                'is_allowed' => true,
            ]);

            if ($request['role_id'] == 1) {
                $this->syncAccessMenuAdmin($userRole->id);
            } else if ($request['role_id'] == 2) {
                $this->syncAccessMenuSpv($userRole->id);
            } else if ($request['role_id'] == 3) {
                $this->syncAccessMenuSales($userRole->id);
            } else if ($request['role_id'] == 4) {
                $this->syncAccessMenuMitra($userRole->id);
            } else if ($request['role_id'] == 5) {
                $this->syncAccessMenuTele($userRole->id);
            }
        }
        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        $data = User::with('roles')->where('id', $id)->first();
        if ($data->enc_pw) {
            $data->password = Crypt::decryptString($data->enc_pw);
        }
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {
        $user = User::where('id', $id)->first();
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role_id' => 'required',
            'nip' => 'required'
        ], [
            'email.unique' => 'Email yang diinput sudah ada.'
        ]);
        $validate['parent_id'] = $request['parent_id'];

        if ($request['password']) {
            $hashedPass = Hash::make($request['password']);
            $validate['password'] = $hashedPass;

            $encPass = Crypt::encryptString($request['password']);
            $validate['enc_pw'] = $encPass;
        }

        $user->update($validate);

        $userRole = UserRole::where('user_id', $user->id)->first();
        $userRole->update([
            'role_id' => $request['role_id'],
        ]);

        UserMenuAccess::where('user_role_id', $userRole->id)->delete();

        if ($request['role_id'] == 1) {
            $this->syncAccessMenuAdmin($userRole->id);
        } else if ($request['role_id'] == 2) {
            $this->syncAccessMenuSpv($userRole->id);
        } else if ($request['role_id'] == 3) {
            $this->syncAccessMenuSales($userRole->id);
        } else if ($request['role_id'] == 4) {
            $this->syncAccessMenuMitra($userRole->id);
        } else if ($request['role_id'] == 5) {
            $this->syncAccessMenuTele($userRole->id);
        }

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {
        User::destroy($id);
        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully',
        ], 201);
    }

    public function getUserSpv() {
        $roleSpv = Role::where("code", "spv")->first();
        $users = User::whereHas('roles', function ($q) use ($roleSpv) {
            $q->where('role_id', $roleSpv->id);
        })->when(auth()->user()->hasRole('Supervisor'), function ($query) {
            $query->where('parent_id', auth()->user()->id);
        })->select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'message' => 'success get Supervisor',
            'data' => $users
        ], 200);
    }

    public function getUserSpvSales() {
        $roleSpv = Role::where("code", "spv")->first();
        $roleSales = Role::where("code", "sls")->first();
        $users = User::whereHas('roles', function ($q) use ($roleSpv, $roleSales) {
            $q->whereIn('role_id', [$roleSpv->id, $roleSales->id]);
        })->when(auth()->user()->hasRole('Supervisor'), function ($query) {
            $query->where('parent_id', auth()->user()->id);
        })->select('id', 'name')->get();

        return response()->json([
            'success' => true,
            'message' => 'success get Supervisor',
            'data' => $users
        ], 200);
    }

    private function syncAccessMenuAdmin($userRole) {
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 1,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 2,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 3,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 4,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 5,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 6,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 7,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 8,
            'is_allowed' => true
        ]);
    }

    private function syncAccessMenuSpv($userRole) {
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 1,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 2,
            'is_allowed' => true
        ]);
        // UserMenuAccess::create([
        //     'user_role_id' => $userRole,
        //     'menu_id' => 3,
        //     'is_allowed' => true
        // ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 4,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 5,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 6,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 7,
            'is_allowed' => true
        ]);
    }

    private function syncAccessMenuSales($userRole) {
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 1,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 2,
            'is_allowed' => true
        ]);
        // UserMenuAccess::create([
        //     'user_role_id' => $userRole,
        //     'menu_id' => 3,
        //     'is_allowed' => true
        // ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 4,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 5,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 6,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 7,
            'is_allowed' => true
        ]);
    }

    private function syncAccessMenuMitra($userRole) {
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 1,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 2,
            'is_allowed' => true
        ]);
        // UserMenuAccess::create([
        //     'user_role_id' => $userRole,
        //     'menu_id' => 3,
        //     'is_allowed' => true
        // ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 4,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 5,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 6,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 7,
            'is_allowed' => true
        ]);
    }

    private function syncAccessMenuTele($userRole) {
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 1,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 2,
            'is_allowed' => true
        ]);
        // UserMenuAccess::create([
        //     'user_role_id' => $userRole,
        //     'menu_id' => 3,
        //     'is_allowed' => true
        // ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 4,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 5,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 6,
            'is_allowed' => true
        ]);
        UserMenuAccess::create([
            'user_role_id' => $userRole,
            'menu_id' => 7,
            'is_allowed' => true
        ]);
    }
}
