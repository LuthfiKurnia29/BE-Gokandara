<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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
        $leaderboardData = []; // Placeholder for actual leaderboard data retrieval logic
        $role = Role::where('name', 'sales')->first();
        $data = UserRole::where('role_id', $role->id)
            ->get()
            ->map(function ($user) use ($dateStart, $dateEnd) {
                // Get transaction data from the user
                $transactionTarget = collect();
                if($dateStart && $dateEnd) {
                    $transactionTarget = Transaksi::where('created_id', $user->user->id)
                    ->whereBetween('created_at', [$dateStart, $dateEnd])
                    ->get();
                }else{
                    $transactionTarget = Transaksi::where('created_id', $user->user->id)
                    ->get();
                }
                $transactionGoal = Transaksi::where('created_id', $user->user->id)
                    ->where('status', 'approved')
                    ->get();
                $revenue = $transactionGoal->sum('grand_total');
                return [
                    'salesId' => $user->user->id,
                    'salesName' => $user->user->name,
                    'salesPhone' => $user->user->phone,
                    'TotalTarget' => $transactionTarget->count(),
                    'TotalGoal' => $transactionGoal->count(),
                    'TotalRevenue' => $revenue,
                    'TargetPercentage' => $transactionTarget->count() > 0 ? ($transactionGoal->count() / $transactionTarget->count()) * 100 : 0,
                ];
            });
        $leaderboardData = $data->toArray();
        return response()->json([
            'message' => 'Leaderboard data retrieved successfully',
            'data' => $leaderboardData, // Uncomment and replace with actual data
        ]);
    }
}
