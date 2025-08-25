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
                'users.id as sales_id',
                'users.name as sales_name', 
                'users.email as sales_email',
                'users.nip as sales_nip',
                DB::raw('COALESCE(target_stats.total_target, 0) as total_target'),
                DB::raw('COALESCE(goal_stats.total_goal, 0) as total_goal'),
                DB::raw('COALESCE(goal_stats.total_revenue, 0) as total_revenue'),
                DB::raw('COALESCE(leads_stats.total_leads, 0) as total_leads'),
                DB::raw('
                    CASE 
                        WHEN COALESCE(target_stats.total_target, 0) > 0 
                        THEN (COALESCE(goal_stats.total_goal, 0) / target_stats.total_target) * 100 
                        ELSE 0 
                    END as target_percentage
                ')
            ])
            ->whereHas('roles', function ($query) use ($salesRoleId) {
                $query->where('role_id', $salesRoleId);
            })
            // Left join for target transactions (with date filter if provided)
            ->leftJoinSub(
                $this->getTargetTransactionsSubquery($dateStart, $dateEnd),
                'target_stats',
                'users.id',
                '=',
                'target_stats.created_id'
            )
            // Left join for approved transactions (goals)
            ->leftJoinSub(
                $this->getGoalTransactionsSubquery(),
                'goal_stats', 
                'users.id',
                '=',
                'goal_stats.created_id'
            )
            // Left join for leads count
            ->leftJoinSub(
                $this->getLeadsSubquery(),
                'leads_stats',
                'users.id', 
                '=',
                'leads_stats.created_id'
            )
            ->orderByDesc('total_leads');

        // Apply pagination
        $leaderboardData = $leaderboardQuery->paginate($perPage, ['*'], 'page', $page);

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

        // Build the optimized query with aggregations for top 3
        $top3Data = User::select([
                'users.id as sales_id',
                'users.name as sales_name', 
                'users.email as sales_email',
                'users.nip as sales_nip',
                DB::raw('COALESCE(target_stats.total_target, 0) as total_target'),
                DB::raw('COALESCE(goal_stats.total_goal, 0) as total_goal'),
                DB::raw('COALESCE(goal_stats.total_revenue, 0) as total_revenue'),
                DB::raw('COALESCE(leads_stats.total_leads, 0) as total_leads'),
                DB::raw('
                    CASE 
                        WHEN COALESCE(target_stats.total_target, 0) > 0 
                        THEN (COALESCE(goal_stats.total_goal, 0) / target_stats.total_target) * 100 
                        ELSE 0 
                    END as target_percentage
                ')
            ])
            ->whereHas('roles', function ($query) use ($salesRoleId) {
                $query->where('role_id', $salesRoleId);
            })
            // Left join for target transactions (with date filter if provided)
            ->leftJoinSub(
                $this->getTargetTransactionsSubquery($dateStart, $dateEnd),
                'target_stats',
                'users.id',
                '=',
                'target_stats.created_id'
            )
            // Left join for approved transactions (goals)
            ->leftJoinSub(
                $this->getGoalTransactionsSubquery(),
                'goal_stats', 
                'users.id',
                '=',
                'goal_stats.created_id'
            )
            // Left join for leads count
            ->leftJoinSub(
                $this->getLeadsSubquery(),
                'leads_stats',
                'users.id', 
                '=',
                'leads_stats.created_id'
            )
            ->orderByDesc('total_leads')
            ->limit(3)
            ->get();

        return response()->json($top3Data);
    }
}
