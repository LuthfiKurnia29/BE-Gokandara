<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FollowupMonitoring;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    //
    public function getCalendar($month = null, $year = null)
    {
        $query = FollowupMonitoring::query();

        // Filter bulan jika parameter ada
        if ($month) {
            $query->whereMonth('first_date', $month);
        }

        // Filter tahun jika parameter ada
        if ($year) {
            $query->whereYear('first_date', $year);
        }
        $result = $query->get();

        return response()->json([
            'success' => true,
            'message' => "Successfully get data calendar",
            'data' => $result
        ], 200);
    }
}
