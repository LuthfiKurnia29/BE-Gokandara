<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FollowupMonitoring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowupMonitoringController extends Controller
{
    public function getAllFollowUps(Request $request)
    {
        $startDate = $request->tanggal_awal;
        $endDate = $request->tanggal_akhir;

        $query = FollowupMonitoring::with('konsumen');

        if ($startDate && $endDate) {
            $query->whereBetween('followup_date', [$startDate, $endDate]);
        }

        $followUps = $query->get();

        return response()->json(
            [
                'message' => 'successfully retrieved all follow-ups',
                'status' => 'success',
                'data' => $followUps,
                'count' => $followUps->count(),
            ],
            200,
        );
    }

    public function ListFollowUp(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;
        $created_id = $request->created_id;

        $data = FollowupMonitoring::with(['konsumen'])
            ->where(function ($query) use ($search, $created_id) {
                if ($created_id) {
                    $query->where('created_id', $created_id);
                }
                if ($search) {
                    $query
                        ->where('followup_date', 'like', "%$search%")
                        ->orWhere('followup_note', 'like', "%$search%")
                        ->orWhere('followup_result', 'like', "%$search%")
                        ->orWhere('followup_last_day', 'like', "%$search%")
                        ->orWhere('color', 'like', "%$search%");
                }
            })
            ->orderBy('id', 'desc')
            ->paginate($per);

        return response()->json($data);
    }

    public function CreateFollowUp(Request $request)
    {
        $user = Auth::user();
        $validate = $request->validate([
            'followup_date' => 'required|date',
            'followup_note' => 'required|string|max:255',
            'konsumen_id' => 'required',
            'prospek_id' => 'required'
            // 'followup_result' => 'required|string|max:255',
            // 'followup_last_day' => 'required|date',
        ]);
        $validate['sales_id'] = $user->id;
        $followup = FollowupMonitoring::create($validate);

        return response()->json(
            [
                'success' => true,
                'message' => 'FollowUp created successfully',
            ],
            201,
        );
    }

    public function UpdateFollowUp(Request $request, $id)
    {
        $user = Auth::user();
        $validate = $request->validate([
            'followup_date' => 'required|date',
            'followup_note' => 'required|string|max:255',
            'followup_result' => 'required|string|max:255',
            'konsumen_id' => 'required',
            'followup_last_day' => 'required|date',
            'prospek_id' => 'required'
            // 'color' => 'required|string',
        ]);

        $validate['sales_id'] = $user->id;

        $followup = FollowupMonitoring::findOrFail($request->id);
        $followup->update($validate);

        return response()->json(
            [
                'success' => true,
                'message' => 'FollowUp updated successfully',
            ],
            200,
        );
    }

    public function DeleteFollowUp($id)
    {
        $followup = FollowupMonitoring::findOrFail($id);
        $followup->delete();

        return response()->json(
            [
                'success' => true,
                'message' => 'FollowUp deleted successfully',
            ],
            200,
        );
    }

    public function updateStatus($id)
    {
        $followup = FollowupMonitoring::findOrFail($id);
        $followup->status = !$followup->status;
        $followup->save();

        return response()->json(
            [
                'success' => true,
                'message' => 'FollowUp status updated successfully',
            ],
            200,
        );
    }
};
