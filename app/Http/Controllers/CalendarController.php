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
        $query = FollowupMonitoring::with(['konsumen', 'prospek'])->whereBetween(FollowupMonitoring::raw('DAY(followup_date)'), [$request->startDay, $request->endDay]);

        // Filter by tanggal
        if (auth()->user->hasRole('Sales')) {
            $query->where('sales_id', auth()->user()->id);
        } else if (isset($request->sales_id)) {
            $query->where('sales_id', $request->sales_id);
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
        $result = FollowupMonitoring::with(['konsumen', 'prospek'])->where('id', $id)->first();

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
            'followup_date' => 'required',
            'followup_note' => 'required',
            'followup_result' => 'required|string|max:255',
            'konsumen_id' => 'required',
            'followup_last_day' => 'required',
            'prospek_id' => 'required'
        ]);
        $data['sales_id'] = $user->id;
        
        $calendar = FollowupMonitoring::create($data);

        $konsumen = Konsumen::find($data['konsumen_id']);
        if($konsumen && $request['prospek_id']) {
            $konsumen->prospek_id = $request['prospek_id'] ?? $konsumen->prospek_id;
            $konsumen->save();
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully created calendar data",
            'data' => $calendar
        ], 201);
    }

    public function updateDataCalendar(Request $request, $id){
        $user = Auth::user();
        $data = $request->validate([
            'followup_date' => 'required',
            'followup_note' => 'required',
            'followup_result' => 'required|string|max:255',
            'konsumen_id' => 'required',
            'followup_last_day' => 'required',
            'prospek_id' => 'required'
        ]);
        $data['sales_id'] = $user->id;
        
        $calendar = FollowupMonitoring::findOrFail($id);

        if($calendar != null){
            $calendar->update($data);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Followup monitoring not found'
            ], 400);
        }

        $konsumen = Konsumen::find($data['konsumen_id']);
        if($konsumen && $request['prospek_id']) {
            $konsumen->prospek_id = $request['prospek_id'] ?? $konsumen->prospek_id;
            $konsumen->save();
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully updated calendar data",
        ], 204);
    }

    public function deleteDataCalendar($id){
        $user = Auth::user();
        $calendar = FollowupMonitoring::destroy($id);

        return response()->json([
            'success' => true,
            'message' => "Successfully deleted calendar data",
        ], 200);
        
    }
}
