<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiwayatPembayaran;

class RiwayatPembayaranController extends Controller
{
    public function index(Request $request, $id)
    {
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;

        $data = RiwayatPembayaran::where('transaksi_id', $id)
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('tanggal', 'like', "%$search%")
                        ->orWhere('nominal', 'like', "%$search%")
                        ->orWhere('keterangan', 'like', "%$search%");

                }
            })
            ->orderBy('id', 'desc')
            ->paginate($per);

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaksi_id' => 'required|exists:transaksis,id',
            'tanggal' => 'required|date',
            'nominal' => 'required|numeric',
            'keterangan' => 'nullable|string',
        ]);

        $riwayat = RiwayatPembayaran::create($validated);

        return response()->json(
            [
                'success' => true,
                'message' => 'Payment history created successfully',
            ],
            201,
        );
    }

    public function update(Request $request, $id)
    {
        $riwayat = RiwayatPembayaran::findOrFail($id);

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'nominal' => 'required|numeric',
            'keterangan' => 'nullable|string',
        ]);

        $riwayat->update($validated);

        return response()->json(
            [
                'success' => true,
                'message' => 'Payment history updated successfully',
            ],
            200,
        );
    }

    public function destroy($id)
    {
        $riwayat = RiwayatPembayaran::findOrFail($id);
        $riwayat->delete();

        return response()->json(
            [
                'success' => true,
                'message' => 'Payment history deleted successfully',
            ],
            200,
        );
    }
}
