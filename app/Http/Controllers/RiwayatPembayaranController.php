<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RiwayatPembayaranController extends Controller
{
    public function getRiwayatPembayaran(Request $request, $id)
    {
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;
        $created_id = $request->created_id;

        $data = RiwayatPembayaran::where('transaksi_id', $id)
            ->where(function ($query) use ($search, $created_id) {
                if ($created_id) {
                    $query->where('created_id', $created_id);
                }
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
}
