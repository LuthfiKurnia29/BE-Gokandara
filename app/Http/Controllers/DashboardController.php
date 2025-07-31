<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FollowupMonitoring;
use App\Models\Konsumen;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function getFollowUpToday()
    {
        $today = Carbon::today()->format('dd-mm-yyyy');
        $followUps = FollowupMonitoring::whereDate('followup_date', $today)
            ->with('konsumen')
            ->get();
        // This method can be used to return a dashboard view or data
        return response()->json([
            'message' => 'successfully retrieved follow-ups for today',
            'status' => 'success',
            'data' => $followUps,
            'count' => $followUps->count()
        ], 200);
    }

    public function getFollowUpTomorrow()
    {
        $startOfWeek = Carbon::now()->addDay()->format('dd-mm-yyyy');
        $followUps = FollowupMonitoring::whereBetween('followup_date', $startOfWeek)
            ->with('konsumen')
            ->get();
        return response()->json([
            'message' => 'successfully retrieved follow-ups for this week',
            'status' => 'success',
            'data' => $followUps,
            'count' => $followUps->count()
        ], 200);
    }

    public function getNewKonsumens(){
        $newKonsumens = Konsumen::whereDate('created_at', Carbon::today())->get();
        return response()->json([
            'message' => 'successfully retrieved new konsumens',
            'status' => 'success',
            'data' => $newKonsumens,
            'count' => $newKonsumens->count()
        ], 200);
    }
}
