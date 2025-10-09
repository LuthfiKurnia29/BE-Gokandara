<?php

namespace App\Http\Controllers;

use App\Models\PembayaranProjeks;
use App\Models\Projek;
use App\Models\ProjekGambar;
use App\Models\Tipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProjekController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;

        $data = Projek::where(function ($query) use ($search) {
            if ($search) {
                $query->where('name', 'like', "%$search%");
            }
        })
            ->orderBy('id', 'desc')
            ->paginate($per);

        return response()->json($data);
    }

    public function allProject(Request $request) {
        $search = $request->search;
        $data = Projek::select('id', 'name', 'address')
            ->when($search, function ($query) use ($search) {
                $query->where('nama', 'like', "%$search%");
            })
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $projek = Projek::create([
            'name' => $request['name'],
            'kavling_total' => $request['jumlah_kavling'],
            'address' => $request['alamat'],
        ]);

        if ($request['tipe']) {
            foreach ($request['tipe'] as $tipe) {
                $tipeModel = Tipe::create([
                    'name' => $tipe['name'],
                    'luas_tanah' => $tipe['luas_tanah'],
                    'luas_bangunan' => $tipe['luas_bangunan'],
                    'jumlah_unit' => $tipe['jumlah_unit'],
                    'project_id' => $projek->id,
                    'harga' => $tipe['harga'] ?? null,
                ]);

                // Support harga per jenis pembayaran
                if (isset($tipe['jenis_pembayaran']) && is_array($tipe['jenis_pembayaran'])) {
                    foreach ($tipe['jenis_pembayaran'] as $jp) {
                        PembayaranProjeks::create([
                            'projek_id' => $projek->id,
                            'tipe_id' => $tipeModel->id,
                            'skema_pembayaran_id' => $jp['id'] ?? null,
                            'harga' => $jp['harga'] ?? null,
                        ]);
                    }
                } elseif (isset($tipe['jenis_pembayaran_ids']) && is_array($tipe['jenis_pembayaran_ids'])) {
                    foreach ($tipe['jenis_pembayaran_ids'] as $pembayaranId) {
                        PembayaranProjeks::create([
                            'projek_id' => $projek->id,
                            'tipe_id' => $tipeModel->id,
                            'skema_pembayaran_id' => $pembayaranId,
                            'harga' => null,
                        ]);
                    }
                }
            }
        }

        if ($request['fasilitas']) {
            foreach ($request['fasilitas'] as $fasilitas) {
                \App\Models\Fasilitas::create([
                    'nama_fasilitas' => $fasilitas['name'],
                    'luas_fasilitas' => $fasilitas['luas'],
                    'projeks_id' => $projek->id,
                ]);
            }
        }

        // Handle multiple image uploads
        if ($request->hasFile('gambars')) {
            foreach ($request->file('gambars') as $gambar) {
                if (!$gambar || !$gambar->isValid()) {
                    continue;
                }
                $path = $gambar->store('projek_images', 'public');
                ProjekGambar::create([
                    'projek_id' => $projek->id,
                    'gambar' => $path,
                ]);
            }
        }

        // Handle logo uploads
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('projek_logo', 'public');
            $projek->update([
                'logo' => $path,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        $projek = Projek::where('id', $id)->first();

        if (!$projek) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        }

        $tipes = Tipe::where('project_id', $projek->id)->get();
        $tipeData = $tipes->map(function ($t) use ($projek) {
            $pembayaran = PembayaranProjeks::where('projek_id', $projek->id)
                ->where('tipe_id', $t->id)
                ->get(['skema_pembayaran_id', 'harga'])
                ->map(function ($p) {
                    return [
                        'id' => $p->skema_pembayaran_id,
                        'harga' => $p->harga,
                    ];
                })
                ->values();

            return [
                'id' => $t->id,
                'name' => $t->name,
                'luas_tanah' => $t->luas_tanah,
                'luas_bangunan' => $t->luas_bangunan,
                'jumlah_unit' => $t->jumlah_unit,
                'unit_terjual' => $t->unit_terjual,
                'harga' => $t->harga,
                'jenis_pembayaran' => $pembayaran,
            ];
        });

        $fasilitas = \App\Models\Fasilitas::where('projeks_id', $projek->id)->get()->map(function ($f) {
            return [
                'name' => $f->nama_fasilitas,
                'luas' => $f->luas_fasilitas,
            ];
        });

        $gambar = ProjekGambar::where('projek_id', $projek->id)->get()->map(function ($g) {
            return [
                'id' => $g->id,
                'gambar' => asset('files/' . $g->gambar),
            ];
        });

        $data = [
            'id' => $projek->id,
            'name' => $projek->name,
            'jumlah_kavling' => $projek->kavling_total,
            'alamat' => $projek->address,
            'tipe' => $tipeData,
            'fasilitas' => $fasilitas,
            'gambar' => $gambar,
            'logo' => $projek->logo_url,
        ];

        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Projek $projek) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {
        $projek = Projek::where('id', $id)->first();

        $projek->update([
            'name' => $request['name'],
            'kavling_total' => $request['jumlah_kavling'],
            'address' => $request['alamat'],
        ]);

        if ($request['tipe']) {
            Tipe::where('project_id', $projek->id)->delete();
            PembayaranProjeks::where('projek_id', $projek->id)->delete();

            foreach ($request['tipe'] as $tipe) {
                $tipeModel = Tipe::create([
                    'name' => $tipe['name'],
                    'luas_tanah' => $tipe['luas_tanah'],
                    'luas_bangunan' => $tipe['luas_bangunan'],
                    'jumlah_unit' => $tipe['jumlah_unit'],
                    'project_id' => $projek->id,
                    'harga' => $tipe['harga'] ?? null,
                ]);

                // Support harga per jenis pembayaran
                if (isset($tipe['jenis_pembayaran']) && is_array($tipe['jenis_pembayaran'])) {
                    foreach ($tipe['jenis_pembayaran'] as $jp) {
                        PembayaranProjeks::create([
                            'projek_id' => $projek->id,
                            'tipe_id' => $tipeModel->id,
                            'skema_pembayaran_id' => $jp['id'] ?? null,
                            'harga' => $jp['harga'] ?? null,
                        ]);
                    }
                } elseif (isset($tipe['jenis_pembayaran_ids']) && is_array($tipe['jenis_pembayaran_ids'])) {
                    foreach ($tipe['jenis_pembayaran_ids'] as $pembayaranId) {
                        PembayaranProjeks::create([
                            'projek_id' => $projek->id,
                            'tipe_id' => $tipeModel->id,
                            'skema_pembayaran_id' => $pembayaranId,
                            'harga' => null,
                        ]);
                    }
                }
            }
        }

        if ($request['fasilitas']) {
            \App\Models\Fasilitas::where('projeks_id', $projek->id)->delete();
            foreach ($request['fasilitas'] as $fasilitas) {
                \App\Models\Fasilitas::create([
                    'nama_fasilitas' => $fasilitas['name'],
                    'luas_fasilitas' => $fasilitas['luas'],
                    'projeks_id' => $projek->id,
                ]);
            }
        }

        if ($request->hasFile('gambars')) {
            foreach ($request->file('gambars') as $gambar) {
                if (!$gambar || !$gambar->isValid()) {
                    continue;
                }
                $path = $gambar->store('projek_images', 'public');
                ProjekGambar::create([
                    'projek_id' => $projek->id,
                    'gambar' => $path,
                ]);
            }
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('projek_logo', 'public');
            $projek->update([
                'logo' => $path,
            ]);
        }

        // Handle multiple image uploads
        //if($request->hasFile('gambars')){
        //    $files = $request->file('gambars');
        //    if(!is_array($files)) $files = [$files];
        //    foreach($files as $gambar){
        //        if(!$gambar || !$gambar->isValid()){
        //            continue;
        //        }
        //        $path = $gambar->store('projek_images', 'public');
        //        ProjekGambar::create([
        //            'projek_id' => $projek->id,
        //            'gambar' => $path,
        //        ]);
        //   }
        //}

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully',
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {
        $projek = Projek::where('id', $id)->first();

        if (!$projek) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        }

        // Delete associated images from storage
        $gambars = ProjekGambar::where('projek_id', $projek->id)->get();
        foreach ($gambars as $gambar) {
            if (Storage::disk('public')->exists($gambar->gambar)) {
                Storage::disk('public')->delete($gambar->gambar);
            }
        }

        PembayaranProjeks::where('projek_id', $projek->id)->delete();
        Tipe::where('project_id', $projek->id)->delete();
        \App\Models\Fasilitas::where('projeks_id', $projek->id)->delete();
        ProjekGambar::where('projek_id', $projek->id)->delete();

        $projek->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully',
        ], 201);
    }

    public function tipeByProjek($id) {
        $tipes = Tipe::with('jenisPembayaran')->where('project_id', $id)
            ->select('id', 'name', 'luas_tanah', 'luas_bangunan', 'jumlah_unit', 'harga', 'unit_terjual')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($tipes);
    }

    /**
     * Delete a specific project image
     */
    public function deleteImage($id) {
        $gambar = ProjekGambar::find($id);

        if (!$gambar) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found',
            ], 404);
        }

        // Delete image from storage
        if (Storage::disk('public')->exists($gambar->gambar)) {
            Storage::disk('public')->delete($gambar->gambar);
        }

        $gambar->delete();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully',
        ], 200);
    }

    /**
     * Add new images to existing project
     */
    public function addImages(Request $request, $id) {
        $projek = Projek::find($id);

        if (!$projek) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        }

        if ($request->hasFile('gambar')) {

            // Hapus semua gambar lama milik projek terlebih dahulu
            $gambars = ProjekGambar::where('projek_id', $projek->id)->get();
            foreach ($gambars as $g) {
                if (Storage::disk('public')->exists($g->gambar)) {
                    Storage::disk('public')->delete($g->gambar);
                }
            }
            ProjekGambar::where('projek_id', $projek->id)->delete();

            foreach ($request->file('gambar') as $gambar) {
                if (!$gambar || !$gambar->isValid()) {
                    continue;
                }
                $path = $gambar->store('projek_images', 'public');

                ProjekGambar::create([
                    'projek_id' => $projek->id,
                    'gambar' => $path,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Images added successfully',
        ], 201);
    }

    public function addLogo(Request $request, $id) {
        $projek = Projek::find($id);

        if (!$projek) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('projek_logo', 'public');
            $projek->update([
                'logo' => $path,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logo added successfully',
        ], 201);
    }


    public function getImages($id) {
        $projek = Projek::find($id);
        return response()->json($projek->gambars);
        if (!$projek) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        }
    }

    /**
     * Get list of payment schemes with prices by project and type.
     */
    public function pembayaranByTipe(Request $request, $projekId, $tipeId) {
        $items = DB::table('pembayaran_projeks')
            ->join('skema_pembayarans', 'pembayaran_projeks.skema_pembayaran_id', '=', 'skema_pembayarans.id')
            ->where('pembayaran_projeks.projek_id', $projekId)
            ->where('pembayaran_projeks.tipe_id', $tipeId)
            ->select([
                'skema_pembayarans.id as id',
                'skema_pembayarans.nama as nama',
                'pembayaran_projeks.harga as harga',
            ])
            ->orderBy('skema_pembayarans.id', 'asc')
            ->get();

        return response()->json($items);
    }
}
