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
        $authUserId = auth()->id();

        // Get user's role IDs
        $userRoleIds = DB::table('user_roles')
            ->where('user_id', $authUserId)
            ->pluck('role_id')
            ->toArray();

        if (empty($userRoleIds)) {
            return response()->json([
                'data' => [],
                'current_page' => 1,
                'per_page' => $per,
                'total' => 0,
                'last_page' => 0,
                'from' => null,
                'to' => null,
                'message' => 'User has no assigned roles'
            ]);
        }

        // Create subquery for sales calculation
        $salesSubquery = DB::table('transaksis')
            ->select([
                DB::raw('targets.id as target_id'),
                DB::raw('COALESCE(SUM(transaksis.grand_total), 0) as total_penjualan')
            ])
            ->rightJoin('targets', function ($join) use ($authUserId) {
                $join->whereRaw('transaksis.created_id = ? AND transaksis.created_at BETWEEN targets.tanggal_awal AND targets.tanggal_akhir', [$authUserId]);
            })
            ->groupBy('targets.id');

        // Main query with optimized joins
        $targets = Target::select([
                'targets.*',
                'roles.name as role_name',
                DB::raw('COALESCE(transaksis.grand_total, 0) as total_penjualan'),
                DB::raw('CASE WHEN COALESCE(total_penjualan, 0) >= targets.min_penjualan THEN 1 ELSE 0 END as is_achieved'),
                DB::raw('CASE WHEN notifikasis.id IS NOT NULL THEN 1 ELSE 0 END as has_claimed'),
                DB::raw('CASE 
                    WHEN targets.min_penjualan > 0 THEN 
                        ROUND((COALESCE(total_penjualan, 0) / targets.min_penjualan) * 100, 2)
                    ELSE 0 
                END as percentage')
            ])
            ->join('roles', 'targets.role_id', '=', 'roles.id')
            ->leftJoinSub($salesSubquery, 'sales_data', function ($join) {
                $join->on('targets.id', '=', 'sales_data.target_id');
            })
            ->leftJoin('notifikasis', function ($join) use ($authUserId) {
                $join->on('notifikasis.target_id', '=', 'targets.id')
                     ->where('notifikasis.user_id', '=', $authUserId);
            })
            ->whereIn('targets.role_id', $userRoleIds)
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('roles.name', 'like', "%$search%")
                        ->orWhere('targets.min_penjualan', 'like', "%$search%")
                        ->orWhere('targets.hadiah', 'like', "%$search%");
                }
            })
            ->havingRaw('COALESCE(total_penjualan, 0) >= targets.min_penjualan')
            ->orderBy('targets.id', 'desc')
            ->paginate($per);

        return response()->json($targets);
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
            ->where('id', auth()->user()->id)->first();

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
