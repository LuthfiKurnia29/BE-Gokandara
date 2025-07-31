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
        $today = Carbon::today()->format('d-m-Y');
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
        $tomorrow = Carbon::now()->addDay()->format('d-m-Y');
        $followUps = FollowupMonitoring::where('followup_date', $tomorrow)
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
