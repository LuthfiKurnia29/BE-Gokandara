<?php

namespace App\Http\Controllers;

use App\Models\Notifikasi;
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
        if (auth()->user()->hasRole('Admin')) {
            $per = $request->per ?? 10;
            $page = $request->page ?? 1;
            $search = $request->search;

            $data = Target::with(['role'])->where(function ($query) use ($search) {
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

        $per = $request->per ?? 10;
        $search = $request->search;
        $authUser = auth()->user();

        // Get user's roles
        $userRoles = $authUser->roles->pluck('role.name')->toArray();

        if (empty($userRoles)) {
            return response()->json([
                'data' => [],
                'message' => 'User has no assigned roles'
            ]);
        }

        // Query targets that match user's role and check achievement
        $targets = Target::with(['role'])
            ->whereHas('role', function ($query) use ($userRoles) {
                $query->whereIn('name', $userRoles);
            })
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->whereHas('role', function ($q) use ($search) {
                        $q->where('name', 'like', "%$search%");
                    })
                        ->orWhere('min_penjualan', 'like', "%$search%")
                        ->orWhere('hadiah', 'like', "%$search%");
                }
            })
            ->get()
            ->map(function ($target) use ($authUser) {
                // Calculate user's total sales for this target period
                $totalPenjualan = Transaksi::where('created_id', $authUser->id)
                    ->whereBetween('created_at', [$target->tanggal_awal, $target->tanggal_akhir])
                    ->sum('grand_total');

                // Check if user achieved the target
                $isAchieved = $totalPenjualan >= $target->min_penjualan;

                // Check if user has already claimed bonus
                $hasClaimed = Notifikasi::where('target_id', $target->id)
                    ->where('user_id', $authUser->id)
                    ->exists();

                // Add calculated fields to target
                $target->total_penjualan = $totalPenjualan;
                $target->is_achieved = $isAchieved;
                $target->has_claimed = $hasClaimed;
                $target->percentage = $target->min_penjualan > 0 ?
                    round(($totalPenjualan / $target->min_penjualan) * 100, 2) : 0;

                return $target;
            })
            // ->filter(function ($target) {
            //     // Only return achieved targets
            //     return $target->is_achieved;
            // })
            ->values();

        // Manual pagination for the filtered collection
        $currentPage = $request->page ?? 1;
        $perPage = $per;
        $total = $targets->count();
        $offset = ($currentPage - 1) * $perPage;
        $paginatedTargets = $targets->slice($offset, $perPage)->values();

        return response()->json([
            'data' => $paginatedTargets,
            'current_page' => (int) $currentPage,
            'per_page' => (int) $perPage,
            'total' => $total,
            'last_page' => ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
        ]);
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
    public function destroy($id) {
        Target::find($id)->delete();

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

        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;

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
                    ->whereBetween('transaksis.created_at', [$target->tanggal_awal, $target->tanggal_akhir]);
            })
            ->whereIn('roles.name', [$target->role->name])
            ->groupBy('users.id', 'users.name', 'users.email') // Add other selected user fields
            ->havingRaw('COALESCE(SUM(transaksis.grand_total), 0) >= ?', [$target->min_penjualan])
            ->paginate();

        return response()->json($users);
    }

    public function getCountAchievedUser(Request $request) {
        $count = Notifikasi::where('jenis_notifikasi', 'claim')->where('is_read', false)->count();

        return response()->json($count);
    }

    public function claimBonus(Request $request, $id) {
        $target = Target::find($id);

        if (!$target) {
            return response()->json(['error' => 'Target not found'], 404);
        }

        $user = User::select([
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
                    ->whereBetween('transaksis.created_at', [$target->tanggal_awal, $target->tanggal_akhir]);
            })
            ->whereIn('roles.name', [$target->role->name])
            ->groupBy('users.id', 'users.name', 'users.email') // Add other selected user fields
            ->havingRaw('COALESCE(SUM(transaksis.grand_total), 0) >= ?', [$target->min_penjualan])
            ->where('users.id', auth()->user()->id)->first();

        if (isset($user->id)) {
            $checkNotif = Notifikasi::where('target_id', $id)->where('user_id', auth()->user()->id)->first();
            if ($checkNotif) {
                return response()->json([
                    'message' => 'Anda sudah melakukan claim bonus untuk Target ini'
                ], 400);
            }

            Notifikasi::create([
                'penerima_id' => 1,
                'target_id' => $id,
                'user_id' => auth()->user()->id,
                'jenis_notifikasi' => 'claim',
                'is_read' => false,
            ]);

            return response()->json([
                'message' => 'Berhasil claim bonus. Silakan tunggu konfirmasi dari Manajer/Admin'
            ]);
        }

        return response()->json([
            'message' => 'Kinerja Anda tidak memenuhi Target yang ditentukan'
        ], 400);
    }
}
