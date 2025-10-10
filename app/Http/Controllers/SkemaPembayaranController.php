<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SkemaPembayaran;

class SkemaPembayaranController extends Controller {
    public function allSkemaPembayaran() {
        $data = SkemaPembayaran::get();
        return response()->json($data);
    }

    public function listSkemaPembayaran(Request $request) {
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;

        $data = SkemaPembayaran::where(function ($query) use ($search) {
            if ($search) {
                $query->where('nama', 'like', "%$search%");
            }
        })
            ->orderBy('id', 'desc')
            ->paginate($per);

        return response()->json($data);
    }

    public function createSkemaPembayaran(Request $request) {
        $validate = $request->validate([
            'nama' => 'required|string|max:255',
            'details' => 'required|array',
            'details.*.nama' => 'required|string|max:255',
            'details.*.persentase' => 'required|numeric|min:0|max:100'
        ]);

        $skema = SkemaPembayaran::create($validate);
        $skema->details()->createMany($validate['details']);

        return response()->json([
            'success' => true,
            'message' => 'Skema Pembayaran created successfully',
        ], 201);
    }

    public function updateSkemaPembayaran(Request $request, $id) {
        $skema = SkemaPembayaran::findOrFail($id);

        $validate = $request->validate([
            'nama' => 'required|string|max:255',
            'details' => 'required|array',
            'details.*.nama' => 'required|string|max:255',
            'details.*.persentase' => 'required|numeric|min:0|max:100'
        ]);

        $skema->update($validate);
        $skema->details()->delete();
        $skema->details()->createMany($validate['details']);

        return response()->json([
            'success' => true,
            'message' => 'Skema Pembayaran updated successfully',
        ]);
    }

    public function deleteSkemaPembayaran($id) {
        $skema = SkemaPembayaran::findOrFail($id);
        $skema->delete();

        return response()->json([
            'success' => true,
            'message' => 'Skema Pembayaran deleted successfully',
        ]);
    }
}
