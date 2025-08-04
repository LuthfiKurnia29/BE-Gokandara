<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FollowupMonitoring;
use App\Models\Konsumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function getCalendarById(Request $request, $id)
    {
        $query = FollowupMonitoring::Find($id);

        $result = $query->get();

        return response()->json([
            'success' => true,
            'message' => "Successfully get data calendar by id",
            'data' => $result
        ], 200);
    }

    public function createDataCalendar(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'followup_date' => 'required|date',
            'followup_note' => 'required',
            'prospek_id' => 'required',
        ]);
        $data['sales_id'] = $user->id;
        if($request['konsumen_id']) {
            $data['konsumen_id'] = $request['konsumen_id'];
        }
        
        $calendar = FollowupMonitoring::create($data);

        // $konsumen = Konsumen::find($data['konsumen_id']);
        // if($konsumen && $request['prospek_id']) {
        //     $konsumen->prospek_id = $request['prospek_id'] ?? $konsumen->prospek_id;
        //     $konsumen->save();
        // }

        return response()->json([
            'success' => true,
            'message' => "Successfully created calendar data",
            'data' => $calendar
        ], 201);
    }

}
