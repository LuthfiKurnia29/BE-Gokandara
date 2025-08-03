<?php

namespace App\Http\Controllers;

use App\Models\Penjualan;
use App\Models\Target;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TargetController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;

        $data = Target::where(function ($query) use ($search) {
            if ($search) {
                $query->whereHas('role', function ($query) use ($search) {
                    $query->where('name', 'like', "%$search%");
                })
                    ->orWhere('min_penjualan', 'like', "%$search%")
                    ->orWhere('hadiah', 'like', "%$search%");
            }
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
        $validate = $request->validate([
            'role_id' => 'required|numeric',
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date',
            'min_penjualan' => 'required|numeric',
            'hadiah' => 'required|string',
        ]);

        Target::create($validate);

        return response()->json([
            'success' => true,
            'message' => 'Target created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        $data = Target::where('id', $id)->first();
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Target $target) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {
        $target = Target::where('id', $id)->first();
        $validate = $request->validate([
            'role_id' => 'required|numeric',
            'tanggal_awal' => 'required|date',
            'tanggal_akhir' => 'required|date',
            'min_penjualan' => 'required|numeric',
            'hadiah' => 'required|string',
        ]);

        $target->update($validate);

        return response()->json([
            'success' => true,
            'message' => 'Target updated successfully',
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(id) {
        Target::destroy(id);

        return response()->json([
            'success' => true,
            'message' => 'Target deleted successfully',
        ], 201);
    }

    public function getAchievedUser(Request $request, $id) {
        $target = Target::find($id);

        if (!$target) {
            return response()->json(['error' => 'Target not found'], 404);
        }

        // Single optimized query with joins and aggregation
        $users = User::select([
            'users.id',
            'users.name',
            'users.email', // Add other user fields you need
            DB::raw('COALESCE(SUM(transaksis.grand_total), 0) as total_penjualan')
        ])
            ->with(['roles.role'])
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->leftJoin('transaksis', function ($join) use ($target) {
                $join->on('transaksis.created_id', '=', 'users.id')
                    ->join('konsumens', 'transaksis.konsumen_id', '=', 'konsumens.id')
                    ->whereBetween('konsumens.tgl_fu_2', [$target->tanggal_awal, $target->tanggal_akhir]);
            })
            ->whereIn('roles.name', ['Supervisor', 'Sales', 'Mitra'])
            ->groupBy('users.id', 'users.name', 'users.email') // Add other selected user fields
            ->havingRaw('COALESCE(SUM(transaksis.grand_total), 0) >= ?', [$target->min_penjualan])
            ->get();

        return response()->json($users);
    }
}
