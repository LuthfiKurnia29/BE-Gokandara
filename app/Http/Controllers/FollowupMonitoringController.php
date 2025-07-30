<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FollowupMonitoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowupMonitoringController extends Controller
{
    //
    public function CreateFollowUp(Request $request)
    {
        $user = Auth::user();
        $validate = $request->validate([
            'followup_date' => 'required|date',
            'followup_note' => 'nullable|string|max:255',
            'followup_result' => 'nullable|string|max:255',
            // 'sales_id' => 'required',
            'konsumen_id' => 'required',
            'followup_last_day' => 'nullable|date'
        ]);
        $validate['sales_id'] = $user->id; // Set sales_id to the authenticated user's ID
        $followup = FollowupMonitoring::create($validate);
        return response()->json([
            'success' => true,
            'message' => "Successfully create follow up",
            'data' => $followup
        ], 201);
    }

    public function UpdateFollowUp(Request $request)
    {

    }
}
