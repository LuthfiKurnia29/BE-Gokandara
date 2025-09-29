<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Properti;
use App\Models\DaftarHarga;
use App\Models\Transaksi;
use App\Models\Konsumen;
use App\Models\Tipe;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TransaksiController extends Controller {
    public function listTransaksi(Request $request) {
        // $user = Auth::user();
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;
        $created_id = $request->created_id;
        $id = auth()->user()->id;
        $data = Transaksi::with(['konsumen', 'properti', 'blok', 'tipe', 'unit', 'createdBy', 'projek'])
            ->where(function ($query) use ($search, $created_id, $id) {
                if ($created_id) {
                    $query->where('created_id', $created_id);
                } else if (auth()->user()->hasRole('Admin')) {
                    $query->where('status', '==', 'Negotiation')
                        ->orWhere('created_id', $id);
                } else {
                    if (auth()->user()->hasRole('Supervisor')) {
                        $query->where('created_id', $id)
                            ->orWhereHas('createdBy', function ($q) use ($id) {
                                $q->where('parent_id', $id);
                            });
                    }
                    if (auth()->user()->hasRole('Telemarketing')) {
                        $query->whereHas('konsumen', function ($q) use ($id) {
                            $q->where('added_by', $id);
                        });
                    } else {
                        $query->where('created_id', $id);
                    }
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

        $mappedData = $data->getCollection()->map(function ($item) {
            $stock = Tipe::where([
                'project_id' => $item->projeks_id,
                'id' => $item->tipe_id,
            ])->first();

            if ($stock) {
                $item->harga_asli = $stock->harga;
            } else {
                $item->harga_asli = 0;
            }

            return $item;
        });

        $data->setCollection($mappedData);

        return response()->json($data);
    }

    public function createTransaksi(Request $request) {
        ini_set('post_max_size', '124M');
        ini_set('upload_max_filesize', '124M');

        $konsumen = Konsumen::where('id', $request->konsumen_id)->first();
        if (is_null($konsumen->ktp_number)) {
            return response()->json([
                'message' => 'Konsumen belum memiliki nomor KTP. Silahkan lengkapi data KTP terlebih dahulu.'
            ], 400);
        }

        $validate = $request->validate([
            'konsumen_id' => 'required',
            'projeks_id' => 'required',
            'skema_pembayaran_id' => 'required',
            'tipe_id' => 'required',
            'kavling_dipesan' => 'required|numeric',
            'diskon' => 'nullable',
            'tipe_diskon' => 'nullable|in:percent,fixed',
            'kelebihan_tanah' => 'nullable|numeric',
            'harga_per_meter' => 'nullable|numeric',
            // 'skema_pembayaran' => 'required|in:Cash Keras,Cash Tempo,Kredit',
            'dp' => 'nullable|numeric',
            'no_transaksi' => 'required|numeric|unique:transaksis,no_transaksi',
            'jangka_waktu' => 'nullable|integer',
        ]);

        $validate['diskon'] = $validate['diskon'] ?? 0;
        $validate['kelebihan_tanah'] = $validate['kelebihan_tanah'] ?? 0;
        $validate['harga_per_meter'] = $validate['harga_per_meter'] ?? 0;

        $validate['created_id'] = isset($request->created_id) ? $request->created_id : Auth::user()->id;
        $validate['updated_id'] = isset($request->created_id) ? $request->created_id : Auth::user()->id;

        $stock = Tipe::where([
            'project_id' => $request->projeks_id,
            'id' => $request->tipe_id,
        ])->first();

        if (!$stock) {
<<<<<<< HEAD
            if (($stock->jumlah_unit - $stock->unit_terjual) < $request->kavling_dipesan) {
=======
            if(($stock->jumlah_unit - $stock->unit_terjual) < $request->kavling_dipesan) {
>>>>>>> origin/main
                return response()->json([
                    'message' => 'Stok tidak tersedia untuk opsi transaksi ini.'
                ], 400);
            }
        }

        if ($request->diskon) {
            if ($request->tipe_diskon == 'percent') {
                $validate['grand_total'] = $stock->harga * $request->kavling_dipesan - (($validate['diskon'] / 100) * $stock->harga);
            } else if ($request->tipe_diskon == 'fixed') {
                $validate['grand_total'] = $stock->harga * $request->kavling_dipesan - $request->diskon;
            } else {
                $validate['grand_total'] = $stock->harga * $request->kavling_dipesan;
            }
        } else {
            $validate['grand_total'] = $stock->harga * $request->kavling_dipesan;
        }

        // Set status berdasarkan role user
        $user = Auth::user();
        $userRoles = $user->roles->pluck('role.name')->toArray();

        if (in_array('Admin', $userRoles)) {
            $validate['status'] = 'Approved';
        } elseif (in_array('Supervisor', $userRoles)) {
            $validate['status'] = 'Negotiation';
        } elseif (in_array('Mitra', $userRoles)) {
            $validate['status'] = 'Negotiation';
        } else {
            $validate['status'] = 'Pending';
        }

        // Update stok unit
        $stock->unit_terjual += $request->kavling_dipesan;
        $stock->save();

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
        $data = Transaksi::with(['konsumen', 'projeks', 'tipe', 'skemaPembayaran', 'createdBy'])
            ->where('id', $id)
            ->first();
        return response()->json($data);
    }


    public function updateTransaksi(Request $request, $id) {
        ini_set('post_max_size', '124M');
        ini_set('upload_max_filesize', '124M');

        $validate = $request->validate([
            'konsumen_id' => 'required',
            'projeks_id' => 'required',
            'skema_pembayaran_id' => 'required',
            'tipe_id' => 'required',
            'kavling_dipesan' => 'required|numeric',
            'diskon' => 'nullable',
            'tipe_diskon' => 'nullable|in:percent,fixed',
            'kelebihan_tanah' => 'nullable|numeric',
            'harga_per_meter' => 'nullable|numeric',
            'dp' => 'nullable|numeric',
            'no_transaksi' => 'required|numeric|unique:transaksis,no_transaksi,' . $id,
        ]);

        $validate['diskon'] = $validate['diskon'] ?? 0;
        $validate['kelebihan_tanah'] = $validate['kelebihan_tanah'] ?? 0;
        $validate['harga_per_meter'] = $validate['harga_per_meter'] ?? 0;

        $validate['created_id'] = isset($request->created_id) ? $request->created_id : Auth::user()->id;
        $validate['updated_id'] = isset($request->created_id) ? $request->created_id : Auth::user()->id;

        $transaksi = Transaksi::where('id', $id)->first();
        $old_kavling = $transaksi->kavling_dipesan;

        $stock = Tipe::where([
            'project_id' => $request->projeks_id,
            'id' => $request->tipe_id,
        ])->first();

        if (($stock->jumlah_unit - $stock->unit_terjual) + $old_kavling < $request->kavling_dipesan) {
            return response()->json([
                'message' => 'Stok tidak tersedia untuk opsi transaksi ini.'
            ], 400);
        }

        if ($request->diskon) {
            if ($request->tipe_diskon == 'percent') {
                $validate['grand_total'] = $stock->harga * $request->kavling_dipesan - (($validate['diskon'] / 100) * $stock->harga);
            } else if ($request->tipe_diskon == 'fixed') {
                $validate['grand_total'] = $stock->harga * $request->kavling_dipesan - $request->diskon;
            } else {
                $validate['grand_total'] = $stock->harga * $request->kavling_dipesan;
            }
        } else {
            $validate['grand_total'] = $stock->harga * $request->kavling_dipesan;
        }

        $user = Auth::user();
        $userRoles = $user->roles->pluck('role.name')->toArray();

        if (in_array('Admin', $userRoles)) {
            $validate['status'] = 'Approved';
        } elseif (in_array('Supervisor', $userRoles)) {
            $validate['status'] = 'Negotiation';
        } elseif (in_array('Mitra', $userRoles)) {
            $validate['status'] = 'Negotiation';
        } else {
            $validate['status'] = 'Pending';
        }

        // Update stok unit
        $stock->unit_terjual = $stock->unit_terjual - $old_kavling + $request->kavling_dipesan;
        $stock->save();

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
        $tipe = Tipe::where('id', $transaksi->tipe_id)->first();
        if ($tipe) {
            $tipe->unit_terjual = max(0, ($tipe->unit_terjual ?? 0) - ($transaksi->kavling_dipesan ?? 0));
            $tipe->save();
        }
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
        if (!auth()->user()->hasRole('Admin')) {
            return response()->json([
                'message' => 'Unauthorized to change status to ' . $validate['status']
            ], 403);
        }
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
