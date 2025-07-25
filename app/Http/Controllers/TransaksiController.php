<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Properti;
use App\Models\Transaksi;

class TransaksiController extends Controller
{
    public function listTransaksi(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;

        $data = Transaksi::with(['konsumen', 'properti', 'blok', 'tipe', 'unit'])
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query
                        ->where('status', 'like', "%$search%")
                        ->orWhereHas('konsumen', function ($q) use ($search) {
                            $q->where('name', 'like', "%$search%")
                                ->orWhere('address', 'like', "%$search%")
                                ->orWhere('ktp_number', 'like', "%$search%")
                                ->orWhere('phone', 'like', "%$search%")
                                ->orWhere('email', 'like', "%$search%")
                                ->orWhere('description', 'like', "%$search%")
                                ->orWhere('pengalaman', 'like', "%$search%")
                                ->orWhere('materi_fu', 'like', "%$search%");
                        })
                        ->orWhereHas('properti', function ($q) use ($search) {
                            $q->where('luas_bangunan', 'like', "%$search%")
                                ->orWhere('luas_tanah', 'like', "%$search%")
                                ->orWhere('lokasi', 'like', "%$search%")
                                ->orWhere('kelebihan', 'like', "%$search%");
                        });
                }
            })
            ->orderBy('id', 'desc')
            ->paginate($per);

        return response()->json($data);
    }

    public function createTransaksi(Request $request)
    {
        $validate = $request->validate([
            'konsumen_id' => 'required',
            'properti_id' => 'required',
            'blok_id' => 'required',
            'tipe_id' => 'required',
            'unit_id' => 'required',
            'diskon' => 'nullable',
        ]);

        $validate['diskon'] = $validate['diskon'] ?? 0;

        $properti = Properti::where('id', $validate['properti_id'])->first();

        $validate['grand_total'] = $properti->harga - ($validate['diskon'] / 100) * $properti->harga;
        $validate['status'] = 'Pending';

        Transaksi::create($validate);

        return response()->json(
            [
                'success' => true,
                'message' => 'Transaksi created successfully',
            ],
            201,
        );
    }

    public function updateTransaksi(Request $request, $id)
    {
        $validate = $request->validate([
            'konsumen_id' => 'required',
            'properti_id' => 'required',
            'blok_id' => 'required',
            'tipe_id' => 'required',
            'unit_id' => 'required',
            'diskon' => 'nullable',
        ]);

        $validate['diskon'] = $validate['diskon'] ?? 0;
        $properti = Properti::where('id', $validate['properti_id'])->first();

        $validate['grand_total'] = $properti->harga - ($validate['diskon'] / 100) * $properti->harga;

        $transaksi = Transaksi::where('id', $id)->first();
        $transaksi->update($validate);

        return response()->json(
            [
                'success' => true,
                'message' => 'Transaksi updated successfully',
            ],
            201,
        );
    }

    public function deleteTransaksi($id)
    {
        $transaksi = Transaksi::findOrFail($id);
        $transaksi->delete();

        return response()->json(
            [
                'success' => true,
                'message' => 'Transaksi deleted successfully',
            ],
            201,
        );
    }

    public function updateStatusTransaksi(Request $request, $id)
    {
        $validate = $request->validate([
            'status' => 'required'
        ]);

        $transaksi = Transaksi::where('id', $id)->first();
        $transaksi->update($validate);

        return response()->json(
            [
                'success' => true,
                'message' => 'Transaction status successfully changed',
            ],
            201,
        );
    }
}
