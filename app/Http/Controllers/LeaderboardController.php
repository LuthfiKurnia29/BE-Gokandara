<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Konsumen;
use App\Models\Role;
use App\Models\Target;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller {
    public function getAllLeaderboard(Request $request) {
        $dateStart = $request->dateStart ?? null;
        $dateEnd = $request->dateEnd ?? null;
        $perPage = $request->per_page ?? 15;
        $page = $request->page ?? 1;

        // Get sales role ID once
        $salesRoleId = Role::where('code', 'sls')->first()->id;

        if (!$salesRoleId) {
            return response()->json([
                'message' => 'Sales role not found',
                'data' => []
            ]);
        }

        // Build the optimized query with aggregations
        // $leaderboardQuery = User::select([
        //     'users.id',
        //     'users.name',
        //     'users.email', // Add other user fields you need
        //     DB::raw('COALESCE(SUM(transaksis.grand_total), 0) as total_revenue'),
        //     DB::raw('COALESCE(COUNT(DISTINCT transaksis.id), 0) as total_goal'),
        //     DB::raw('COALESCE(COUNT(DISTINCT konsumens.id), 0) as total_leads'),
        //     // DB::raw('COALESCE(SUM(targets.min_penjualan), 0) as total_target'),
        // ])
        //     ->with(['roles.role'])
        //     // ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
        //     // ->join('roles', 'user_roles.role_id', '=', 'roles.id')
        //     ->leftJoin('transaksis', function ($join) use ($dateStart, $dateEnd) {
        //         $join->on('transaksis.created_id', '=', 'users.id')
        //             ->where('transaksis.status', '=', 'Akad');
        //         if (isset($dateStart) && isset($dateEnd)) {
        //             $join->whereBetween('transaksis.created_at', [$dateStart, $dateEnd]);
        //         }
        //     })
        //     ->leftJoin('konsumens', function ($join) use ($dateStart, $dateEnd) {
        //         $join->on('konsumens.created_id', '=', 'users.id');
        //         if (isset($dateStart) && isset($dateEnd)) {
        //             $join->whereBetween('konsumens.created_at', [$dateStart, $dateEnd]);
        //         }
        //     })
        //     // ->leftJoin('targets', function ($join) use ($dateStart, $dateEnd) {
        //     //     $join->on('targets.role_id', '=', 'user_roles.role_id');
        //     //     if (isset($dateStart) && isset($dateEnd)) {
        //     //         $join->where(function ($query) use ($dateStart, $dateEnd) {
        //     //             $query->whereBetween('targets.tanggal_awal', [$dateStart, $dateEnd])
        //     //                 ->orWhereBetween('targets.tanggal_akhir', [$dateStart, $dateEnd])
        //     //                 ->orWhere(function ($subQuery) use ($dateStart, $dateEnd) {
        //     //                     $subQuery->where('targets.tanggal_awal', '<=', $dateStart)
        //     //                         ->where('targets.tanggal_akhir', '>=', $dateEnd);
        //     //                 });
        //     //         });
        //     //     }
        //     // })
        //     ->whereHas('roles', function ($query) use ($salesRoleId) {
        //         $query->where('role_id', $salesRoleId);
        //     })
        //     ->groupBy('users.id', 'users.name', 'users.email');

        $leaderboardQuery = User::withSum(['transaksis' => function ($query) use ($dateStart, $dateEnd) {
            $query->where('status', 'Akad');
            if (isset($dateStart) && isset($dateEnd)) {
                $query->whereBetween('created_at', [$dateStart, $dateEnd]);
            }
        }], 'grand_total')
            ->withCount(['transaksis' => function ($query) use ($dateStart, $dateEnd) {
                $query->where('status', 'Akad');
                if (isset($dateStart) && isset($dateEnd)) {
                    $query->whereBetween('created_at', [$dateStart, $dateEnd]);
                }
            }], 'id')
            ->withCount(['konsumens' => function ($query) use ($dateStart, $dateEnd) {
                $query->whereBetween('created_at', [$dateStart, $dateEnd]);
            }], 'id')->whereHas('roles', function ($query) use ($salesRoleId) {
                $query->where('role_id', $salesRoleId);
            })->groupBy('users.id', 'users.name', 'users.email');

        // Apply pagination
        $leaderboardData = $leaderboardQuery->paginate($perPage, ['*'], 'page', $page);

        $data = $leaderboardData->getCollection()->map(function ($item) use ($dateStart, $dateEnd) {
            $minPenjualan = Target::where('role_id', $item->roles[0]->role_id)->where(function ($query) use ($dateStart, $dateEnd) {
                $query->whereBetween('tanggal_awal', [$dateStart, $dateEnd])
                    ->orWhereBetween('tanggal_akhir', [$dateStart, $dateEnd]);
            })->sum('min_penjualan');
            $item->target_percentage = $minPenjualan > 0 ? number_format($item->transaksis_sum_grand_total / $minPenjualan * 100, 2) : 0;

            $item->total_revenue = $item->transaksis_sum_grand_total;
            $item->total_goal = $item->transaksis_count_id;
            $item->total_leads = $item->konsumens_sum_id;

            $item->sales_id = $item->id;
            $item->sales_name = $item->name;
            $item->sales_email = $item->email;
            $item->sales_nip = $item->nip;
            return $item;
        });

        $leaderboardData->setCollection($data);

        return response()->json($leaderboardData);
    }

    /**
     * Get subquery for target transactions
     */
    private function getTargetTransactionsSubquery($dateStart = null, $dateEnd = null) {
        $query = Transaksi::select([
            'created_id',
            DB::raw('COUNT(*) as total_target')
        ])->groupBy('created_id');

        if ($dateStart && $dateEnd) {
            $query->whereBetween('created_at', [$dateStart, $dateEnd]);
        }

        return $query;
    }

    /**
     * Get subquery for approved transactions (goals)
     */
    private function getGoalTransactionsSubquery() {
        return Transaksi::select([
            'created_id',
            DB::raw('COUNT(*) as total_goal'),
            DB::raw('SUM(grand_total) as total_revenue')
        ])
            ->where('status', 'approved')
            ->groupBy('created_id');
    }

    /**
     * Get subquery for leads count
     */
    private function getLeadsSubquery() {
        return Konsumen::select([
            'created_id',
            DB::raw('COUNT(*) as total_leads')
        ])->groupBy('created_id');
    }

    /**
     * Get top 3 leaderboard
     */
    public function getTop3Leaderboard(Request $request) {
        $dateStart = $request->dateStart ?? null;
        $dateEnd = $request->dateEnd ?? null;

        // Get sales role ID once
        $salesRoleId = Role::where('code', 'sls')->first()->id;

        if (!$salesRoleId) {
            return response()->json([
                'message' => 'Sales role not found',
                'data' => []
            ]);
        }

        // Build the optimized query with aggregations
        // $leaderboardQuery = User::select([
        //     'users.id',
        //     'users.name',
        //     'users.email', // Add other user fields you need
        //     DB::raw('COALESCE(SUM(transaksis.grand_total), 0) as total_revenue'),
        //     DB::raw('COALESCE(COUNT(DISTINCT transaksis.id), 0) as total_goal'),
        //     DB::raw('COALESCE(COUNT(DISTINCT konsumens.id), 0) as total_leads'),
        //     // DB::raw('COALESCE(SUM(targets.min_penjualan), 0) as total_target'),
        // ])
        //     ->with(['roles.role'])
        //     // ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
        //     // ->join('roles', 'user_roles.role_id', '=', 'roles.id')
        //     ->leftJoin('transaksis', function ($join) use ($dateStart, $dateEnd) {
        //         $join->on('transaksis.created_id', '=', 'users.id')
        //             ->where('transaksis.status', '=', 'Akad');
        //         if (isset($dateStart) && isset($dateEnd)) {
        //             $join->whereBetween('transaksis.created_at', [$dateStart, $dateEnd]);
        //         }
        //     })
        //     ->leftJoin('konsumens', function ($join) use ($dateStart, $dateEnd) {
        //         $join->on('konsumens.created_id', '=', 'users.id');
        //         if (isset($dateStart) && isset($dateEnd)) {
        //             $join->whereBetween('konsumens.created_at', [$dateStart, $dateEnd]);
        //         }
        //     })
        //     // ->leftJoin('targets', function ($join) use ($dateStart, $dateEnd) {
        //     //     $join->on('targets.role_id', '=', 'user_roles.role_id');
        //     //     if (isset($dateStart) && isset($dateEnd)) {
        //     //         $join->where(function ($query) use ($dateStart, $dateEnd) {
        //     //             $query->whereBetween('targets.tanggal_awal', [$dateStart, $dateEnd])
        //     //                 ->orWhereBetween('targets.tanggal_akhir', [$dateStart, $dateEnd])
        //     //                 ->orWhere(function ($subQuery) use ($dateStart, $dateEnd) {
        //     //                     $subQuery->where('targets.tanggal_awal', '<=', $dateStart)
        //     //                         ->where('targets.tanggal_akhir', '>=', $dateEnd);
        //     //                 });
        //     //         });
        //     //     }
        //     // })
        //     ->whereHas('roles', function ($query) use ($salesRoleId) {
        //         $query->where('role_id', $salesRoleId);
        //     })
        //     ->groupBy('users.id', 'users.name', 'users.email')
        //     ->orderByDesc('total_revenue')
        //     ->limit(3)
        //     ->get();

        $leaderboardQuery = User::withSum(['transaksis' => function ($query) use ($dateStart, $dateEnd) {
            $query->where('status', 'Akad');
            if (isset($dateStart) && isset($dateEnd)) {
                $query->whereBetween('created_at', [$dateStart, $dateEnd]);
            }
        }], 'grand_total')
            ->withCount(['transaksis' => function ($query) use ($dateStart, $dateEnd) {
                $query->where('status', 'Akad');
                if (isset($dateStart) && isset($dateEnd)) {
                    $query->whereBetween('created_at', [$dateStart, $dateEnd]);
                }
            }], 'id')
            ->withCount(['konsumens' => function ($query) use ($dateStart, $dateEnd) {
                $query->whereBetween('created_at', [$dateStart, $dateEnd]);
            }], 'id')->whereHas('roles', function ($query) use ($salesRoleId) {
                $query->where('role_id', $salesRoleId);
            })->groupBy('users.id', 'users.name', 'users.email')->get();

        $leaderboardQuery = $leaderboardQuery->map(function ($item) use ($dateStart, $dateEnd) {
            $minPenjualan = Target::where('role_id', $item->roles[0]->role_id)->where(function ($query) use ($dateStart, $dateEnd) {
                $query->whereBetween('tanggal_awal', [$dateStart, $dateEnd])
                    ->orWhereBetween('tanggal_akhir', [$dateStart, $dateEnd]);
            })->sum('min_penjualan');
            $item->target_percentage = $minPenjualan > 0 ? number_format($item->transaksis_sum_grand_total / $minPenjualan * 100, 2) : 0;

            $item->total_revenue = $item->transaksis_sum_grand_total;
            $item->total_goal = $item->transaksis_count_id;
            $item->total_leads = $item->konsumens_sum_id;

            $item->sales_id = $item->id;
            $item->sales_name = $item->name;
            $item->sales_email = $item->email;
            $item->sales_nip = $item->nip;
            return $item;
        })->sortByDesc('transaksis_sum_grand_total')->take(3)->values();

        return response()->json($leaderboardQuery);
    }
}
