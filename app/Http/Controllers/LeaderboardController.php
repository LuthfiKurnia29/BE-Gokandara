<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Konsumen;
use App\Models\Role;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaderboardController extends Controller
{
    public function getAllLeaderboard(Request $request)
    {
        $dateStart = $request->dateStart ?? null;
        $dateEnd = $request->dateEnd ?? null;
        $perPage = $request->per_page ?? 15;
        $page = $request->page ?? 1;

        // Get sales role ID once
        $salesRoleId = Role::where('code', 'sls')->value('id');
        
        if (!$salesRoleId) {
            return response()->json([
                'message' => 'Sales role not found',
                'data' => []
            ]);
        }

        // Build the optimized query with aggregations
        $leaderboardQuery = User::select([
            'users.id',
            'users.name',
            'users.email', // Add other user fields you need
            DB::raw('COALESCE(SUM(transaksis.grand_total), 0) as total_revenue'),
            DB::raw('COALESCE(COUNT(transaksis.id), 0) as total_goal'),
            DB::raw('COALESCE(COUNT(konsumens.id), 0) as total_leads'),
            DB::raw('COALESCE(SUM(targets.min_penjualan), 0) as total_target'),
        ])
            ->with(['roles.role'])
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->leftJoin('transaksis', function ($join) use ($dateStart, $dateEnd) {
                $join->on('transaksis.created_id', '=', 'users.id')
                    ->whereBetween('transaksis.created_at', [$dateStart, $dateEnd]);
            })
            ->leftJoin('konsumens', function ($join) use ($dateStart, $dateEnd) {
                $join->on('transaksis.konsumen_id', '=', 'konsumens.id')
                    ->whereBetween('konsumens.created_at', [$dateStart, $dateEnd]);
            })
            ->leftJoin('targets', function ($join) use ($dateStart, $dateEnd) {
                $join->on('targets.role_id', '=', 'user_roles.user_id')
                    ->whereBetween('targets.created_at', [$dateStart, $dateEnd]);
            })
            ->whereIn('roles.id', [$salesRoleId])
            ->groupBy('users.id', 'users.name', 'users.email');

        // Apply pagination
        $leaderboardData = $leaderboardQuery->paginate($perPage, ['*'], 'page', $page);

        $leaderboardData = $leaderboardData->getCollection()->map(function ($item) {
            $item->target_percentage = $item->total_target > 0 ? $item->total_goal / $item->total_target * 100 : 0;
            return $item;
        });

        return response()->json($leaderboardData);
    }

    /**
     * Get subquery for target transactions
     */
    private function getTargetTransactionsSubquery($dateStart = null, $dateEnd = null)
    {
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
    private function getGoalTransactionsSubquery()
    {
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
    private function getLeadsSubquery()
    {
        return Konsumen::select([
            'created_id',
            DB::raw('COUNT(*) as total_leads')
        ])->groupBy('created_id');
    }

    /**
     * Get top 3 leaderboard
     */
    public function getTop3Leaderboard(Request $request)
    {
        $dateStart = $request->dateStart ?? null;
        $dateEnd = $request->dateEnd ?? null;
        
        // Get sales role ID once
        $salesRoleId = Role::where('code', 'sls')->value('id');
        
        if (!$salesRoleId) {
            return response()->json([
                'message' => 'Sales role not found',
                'data' => []
            ]);
        }

        // Build the optimized query with aggregations
        $leaderboardQuery = User::select([
            'users.id',
            'users.name',
            'users.email', // Add other user fields you need
            DB::raw('COALESCE(SUM(transaksis.grand_total), 0) as total_revenue'),
            DB::raw('COALESCE(COUNT(transaksis.id), 0) as total_goal'),
            DB::raw('COALESCE(COUNT(konsumens.id), 0) as total_leads'),
            DB::raw('COALESCE(SUM(targets.min_penjualan), 0) as total_target'),
        ])
            ->with(['roles.role'])
            ->join('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->join('roles', 'user_roles.role_id', '=', 'roles.id')
            ->leftJoin('transaksis', function ($join) use ($dateStart, $dateEnd) {
                $join->on('transaksis.created_id', '=', 'users.id')
                    ->whereBetween('transaksis.created_at', [$dateStart, $dateEnd]);
            })
            ->leftJoin('konsumens', function ($join) use ($dateStart, $dateEnd) {
                $join->on('transaksis.konsumen_id', '=', 'konsumens.id')
                    ->whereBetween('konsumens.created_at', [$dateStart, $dateEnd]);
            })
            ->leftJoin('targets', function ($join) use ($dateStart, $dateEnd) {
                $join->on('targets.role_id', '=', 'user_roles.user_id')
                    ->whereBetween('targets.created_at', [$dateStart, $dateEnd]);
            })
            ->whereIn('roles.id', [$salesRoleId])
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_leads')
            ->limit(3)
            ->get();

        $leaderboardData = $leaderboardData->map(function ($item) {
            $item->target_percentage = $item->total_target > 0 ? $item->total_goal / $item->total_target * 100 : 0;
            return $item;
        });

        return response()->json($leaderboardData);
    }
}
