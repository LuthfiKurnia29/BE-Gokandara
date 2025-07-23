<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Properti;
use App\Models\Transaksi;

class TransaksiController extends Controller
{
    public function createTransaksi(Request $request) 
    {
        $validate = $request->validate([
            'konsumen_id' => 'required',
            'properti_id' => 'required',
            'diskon' => 'nullable',
        ]);

        $properti = Properti::where('id', $validate['properti_id'])->first();

        $validate['grand_total'] = $properti->harga - (($validate['diskon'] / 100) * $properti->harga);
        $validate['status'] = "Pending";

        Transaksi::create($validate);

        return response()->json([
            'success' => true,
            'message' => 'Transaksi created successfully',
        ], 201);
    }
}
