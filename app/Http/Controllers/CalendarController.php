<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FollowupMonitoring;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    //
    public function getCalendar(Request $request)
    {
        $query = FollowupMonitoring::query();

        // Filter by tanggal
        if ($request) {
            $query->whereBetween(FollowupMonitoring::raw('DAY(followup_date)'), [$request->startDay, $request->endDay]);
        }

        $result = $query->get();

        return response()->json([
            'success' => true,
            'message' => "Successfully get data calendar",
            'data' => $result
        ], 200);
    }
}
