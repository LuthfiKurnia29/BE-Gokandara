<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Konsumen;
use App\Models\Role;
use App\Models\Transaksi;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    //
    public function getAllLeaderboard(Request $request)
    {
        $dateStart = $request->dateStart ?? null;
        $dateEnd = $request->dateEnd ?? null;

        $role = Role::where('name', 'sales')->first();

        $users = User::whereHas('roles', function ($query) use ($role) {
            $query->where('role_id', $role->id);
        })->get();

        $data = $users->map(function ($user) use ($dateStart, $dateEnd) {
            $transactionTarget = $dateStart && $dateEnd
                ? Transaksi::where('created_id', $user->id)->whereBetween('created_at', [$dateStart, $dateEnd])->get()
                : Transaksi::where('created_id', $user->id)->get();

            $transactionGoal = Transaksi::where('created_id', $user->id)
                ->where('status', 'approved')
                ->get();

            $revenue = $transactionGoal->sum('grand_total');
            $totalLeads = Konsumen::where('created_id', $user->id)->count();

            return [
                'sales_id' => $user->id,
                'sales_name' => $user->name,
                'sales_phone' => $user->phone,
                'total_target' => $transactionTarget->count(),
                'total_goal' => $transactionGoal->count(),
                'total_revenue' => $revenue,
                'target_percentage' => $transactionTarget->count() > 0 ? ($transactionGoal->count() / $transactionTarget->count()) * 100 : 0,
                'total_leads' => $totalLeads,
            ];
        });

        // Sort by total_leads descending
        $leaderboardData = $data->sortByDesc('total_leads')->values()->toArray();

        return response()->json([
            'message' => 'Leaderboard data retrieved successfully',
            'data' => $leaderboardData,
        ]);
    }   
}
