<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Properti;
use App\Models\DaftarHarga;
use App\Models\Transaksi;
use App\Models\Konsumen;
use Illuminate\Container\Attributes\Auth;

class TransaksiController extends Controller {
    public function listTransaksi(Request $request) {
        // $user = Auth::user();
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;
        $created_id = $request->created_id;
        $id = auth()->user()->id;
        // if()
        $data = Transaksi::with(['konsumen', 'properti', 'blok', 'tipe', 'unit', 'createdBy'])
            ->where(function ($query) use ($search, $created_id, $id) {
                if ($created_id) {
                    $query->where('created_id', $created_id);
                } else if (!auth()->user()->hasRole('Admin')){
                    $query->where('created_id', $id);
                }
                if ($search) {
                    $query
                        ->where('status', 'like', "%$search%")
                        ->orWhere('no_transaksi', 'like', "%$search%")
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

    public function createTransaksi(Request $request) {
        $konsumen = Konsumen::where('id', $request->konsumen_id)->first();
        if (is_null($konsumen->ktp_number)) {
            return response()->json([
                'message' => 'Konsumen belum memiliki nomor KTP. Silahkan lengkapi data KTP terlebih dahulu.'
            ], 400);
        }

        $validate = $request->validate([
            'konsumen_id' => 'required',
            'properti_id' => 'required',
            'skema_pembayaran_id' => 'required',
            'blok_id' => 'required',
            'tipe_id' => 'required',
            'unit_id' => 'required',
            'diskon' => 'nullable',
            'tipe_diskon' => 'nullable|in:percent,fixed',
            // 'skema_pembayaran' => 'required|in:Cash Keras,Cash Tempo,Kredit',
            'dp' => 'nullable|numeric',
            'no_transaksi' => 'required|numeric|unique:transaksis,no_transaksi',
            'jangka_waktu' => 'nullable|integer',
        ]);

        $validate['diskon'] = $validate['diskon'] ?? 0;
        $validate['created_id'] = auth()->user()->id;
        $validate['updated_id'] = auth()->user()->id;

        $properti = Properti::where('id', $validate['properti_id'])->first();
        $harga = DaftarHarga::where([
            'properti_id' => $properti->id,
            'tipe_id' => $request->tipe_id,
            'unit_id' => $request->unit_id,
        ])->first();

        if (!$harga) {
            return response()->json([
                'message' => 'Daftar Harga tidak tersedia untuk opsi transaksi ini.'
            ], 400);
        }

        if ($request->diskon) {
            if ($request->tipe_diskon == 'percent') {
                $validate['grand_total'] = $harga->harga - ($validate['diskon'] / 100) * $harga->harga;
            } else if ($request->tipe_diskon == 'fixed') {
                $validate['grand_total'] = $harga->harga - $request->diskon;
            } else {
                $validate['grand_total'] = $harga->harga;
            }
        } else {
            $validate['grand_total'] = $harga->harga;
        }

        if (auth()->user()->hasRole('Mitra')) {
            $validate['status'] = 'Negotiation';
        } else {
            $validate['status'] = 'Pending';
        }

        Transaksi::create($validate);

        return response()->json(
            [
                'success' => true,
                'message' => 'Transaksi created successfully',
            ],
            201,
        );
    }

    /**
     * Display the specified resource.
     */
    public function getTransaksi(string $id) {
        $data = Transaksi::where('id', $id)->first();
        return response()->json($data);
    }


    public function updateTransaksi(Request $request, $id) {
        $validate = $request->validate([
            'konsumen_id' => 'required',
            'skema_pembayaran_id' => 'required',
            'properti_id' => 'required',
            'blok_id' => 'required',
            'tipe_id' => 'required',
            'unit_id' => 'required',
            'diskon' => 'nullable',
            'tipe_diskon' => 'nullable|in:percent,fixed',
            // 'skema_pembayaran' => 'required|in:Cash Keras,Cash Tempo,Kredit',
            'dp' => 'nullable|numeric',
            'no_transaksi' => 'required|numeric|unique:transaksis,no_transaksi,' . $id,
            'jangka_waktu' => 'nullable|integer',
        ]);

        $validate['diskon'] = $validate['diskon'] ?? 0;
        $validate['updated_id'] = auth()->user()->id;
        $properti = Properti::where('id', $validate['properti_id'])->first();
        $harga = DaftarHarga::where([
            'properti_id' => $properti->id,
            'tipe_id' => $request->tipe_id,
            'unit_id' => $request->unit_id,
        ])->first();

        if (!$harga) {
            return response()->json([
                'message' => 'Daftar Harga tidak tersedia untuk opsi transaksi ini.'
            ], 400);
        }

        if ($request->diskon) {
            if ($request->tipe_diskon == 'percent') {
                $validate['grand_total'] = $harga->harga - ($validate['diskon'] / 100) * $harga->harga;
            } else if ($request->tipe_diskon == 'fixed') {
                $validate['grand_total'] = $harga->harga - $request->diskon;
            } else {
                $validate['grand_total'] = $harga->harga;
            }
        } else {
            $validate['grand_total'] = $harga->harga;
        }

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

    public function deleteTransaksi($id) {
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

    public function updateStatusTransaksi(Request $request, $id) {
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

    public function historyTransaksi(Request $request, $properti_id) {
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;

        $data = Transaksi::with(['konsumen', 'properti', 'blok', 'tipe', 'unit', 'createdBy'])
            ->where('properti_id', $properti_id)
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query
                        ->where('status', 'like', "%$search%")
                        ->orWhere('no_transaksi', 'like', "%$search%")
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
            ->take(3)
            ->get();

        return response()->json($data);
    }
}
