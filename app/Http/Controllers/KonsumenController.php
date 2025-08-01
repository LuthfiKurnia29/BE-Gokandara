<?php

namespace App\Http\Controllers;

use App\Models\Konsumen;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KonsumenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        // var_dump($user); die;
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;
        $created_id = $request->created_id;
        $userRole = UserRole::with('role', 'user')->where('user_id', $user->id)->first();

        $data = Konsumen::with(['projek', 'prospek'])
            ->where(function ($query) use ($search, $created_id, $user, $userRole) {
                if ($userRole->role->name === 'supervisor') {
                    // Get All Sales under Supervisor
                    $sales = User::where('parent_id', $user->id)->pluck('id');
                    $query->where(function ($q) use ($user, $sales) {
                        $q->where('added_by', $user->id)
                          ->orWhereIn('added_by', $sales);
                    });
                } elseif ($userRole->role->name === 'sales') {
                    $query->where('added_by', Auth::user()->id);
                }
                if ($created_id) {
                    $query->where('created_id', $created_id);
                }
                if ($search) {
                    $query->where('name', 'like', "%$search%")
                        ->orWhere('address', 'like', "%$search%")
                        ->orWhere('ktp_number', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%")
                        ->orWhere('pengalaman', 'like', "%$search%")
                        ->orWhere('materi_fu', 'like', "%$search%");
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
        $user = Auth::user();
        // var_dump($user); die;
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:konsumens',
            'phone' => 'required|string|max:15',
            'ktp_number' => 'required|string|max:16|unique:konsumens',
            'address' => 'required|string|max:255',
            'project_id' => 'required',
            'refrensi_id' => 'required',
            'prospek_id' => 'required',
            'kesiapan_dana' => 'required|numeric|min:0',
            // 'added_by' => $user->id,
            'description' => 'required|string',
            'pengalaman' => 'required|string|max:255',
            'materi_fu_1' => 'required|string',
            'tgl_fu_1' => 'required|string',
            'materi_fu_2' => 'required|string',
            'tgl_fu_2' => 'required|string',
        ]);
        
        $validate['tgl_fu_1'] = Carbon\Carbon::parse($validate['tgl_fu_1'])->format('Y-m-d');
        $validate['tgl_fu_2'] = Carbon\Carbon::parse($validate['tgl_fu_2'])->format('Y-m-d');
        $validate['added_by'] = $user->id;
        $validate['created_id'] = auth()->user()->id;
        $validate['updated_id'] = auth()->user()->id;
        Konsumen::create($validate);

        return response()->json([
            'success' => true,
            'message' => 'Konsumen created successfully',
        ], 201);
    }

    public function allKonsumen(Request $request)
    {
        $search = $request->search;
        $data = Konsumen::select('id', 'name')
                ->when($search, function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                })
                ->orderBy('id', 'desc')
                ->get();

        return response()->json($data);
    }

    public function allKonsumenBySales(Request $request)
    {
        $user = Auth::user();
        // Get all konsumen created by the authenticated user
        $search = $request->search;
        $data = Konsumen::where('added_by', auth()->user()->id)
                        ->orderBy('id', 'desc')
                        ->get();

        return response()->json($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = Konsumen::with(['projek', 'prospek'])->where('id', $id)->first();
        return response()->json($data);   
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Konsumen $konsumen)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $konsumen = Konsumen::where('id', $id)->first();
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:15',
            'ktp_number' => 'required|string|max:16',
            'address' => 'required|string|max:255',
            'project_id' => 'required',
            'refrensi_id' => 'required',
            'prospek_id' => 'required',
            'kesiapan_dana' => 'required|numeric|min:0',
            // 'added_by' => $user->id,
            'description' => 'nullable|string',
            'pengalaman' => 'nullable|string|max:255',
            'materi_fu_1' => 'required|string',
            'tgl_fu_1' => 'required|string',
            'materi_fu_2' => 'required|string',
            'tgl_fu_2' => 'required|string',
        ]);
        $validate['added_by'] = $user->id;
        $validate['updated_id'] = auth()->user()->id;
        $konsumen->update($validate);

        return response()->json([
            'success' => true,
            'message' => 'Konsumen updated successfully',
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Konsumen::destroy($id);

        return response()->json([
            'success' => true,
            'message' => 'Konsumen deleted successfully',
        ], 201);
    }

    public function konsumenBySales(Request $request)
    {
        $user = Auth::user();
        $data = Konsumen::where('added_by', $user->id)
                        ->with(['projek', 'prospek'])
                        ->orderBy('id', 'desc')
                        ->get();
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Konsumen retrieved successfully',
        ]);
    }

    public function konsumenBySupervisor(Request $request)
    {
        $user = Auth::user();
        $sales = User::where('parent_id', $user->id)
                    ->pluck('id');

        $data = Konsumen::where(function($q) use ($user, $sales) {
                        $q->where('added_by', $user->id)
                        ->orWhereIn('added_by', $sales);
                    })
                    ->with(['projek', 'prospek'])
                    ->orderBy('id', 'desc')
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Konsumen retrieved successfully',
        ]);
    }
}
