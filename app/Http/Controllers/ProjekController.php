<?php

namespace App\Http\Controllers;

use App\Models\PembayaranProjeks;
use App\Models\Projek;
use App\Models\Tipe;
use Illuminate\Http\Request;

class ProjekController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
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

    public function allProject(Request $request){
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $projek = Projek::create([
            'name' => $request['name'],
            'kavling_total' => $request['jumlah_kavling'],
            'address' => $request['alamat'],
        ]);

        if($request['tipe']){
            foreach($request['tipe'] as $tipe){
                $tipeModel = Tipe::create([
                    'name' => $tipe['name'],
                    'luas_tanah' => $tipe['luas_tanah'],
                    'luas_bangunan' => $tipe['luas_bangunan'],
                    'jumlah_unit' => $tipe['jumlah_unit'],
                    'project_id' => $projek->id,
                    'harga' => $tipe['harga'],
                ]);

                if(isset($tipe['jenis_pembayaran_ids']) && is_array($tipe['jenis_pembayaran_ids'])){
                    foreach($tipe['jenis_pembayaran_ids'] as $pembayaranId){
                        PembayaranProjeks::create([
                            'projek_id' => $projek->id,
                            'tipe_id' => $tipeModel->id,
                            'skema_pembayaran_id' => $pembayaranId,
                        ]);
                    }
                }
            }
        }

        if($request['fasilitas']){
            foreach($request['fasilitas'] as $fasilitas){
                \App\Models\Fasilitas::create([
                    'nama_fasilitas' => $fasilitas['name'],
                    'luas_fasilitas' => $fasilitas['luas'],
                    'projeks_id' => $projek->id,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $projek = Projek::where('id', $id)->first();

        if (!$projek) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        }

        $tipes = Tipe::where('project_id', $projek->id)->get();
        $tipeData = $tipes->map(function ($t) use ($projek) {
            $pembayaranIds = PembayaranProjeks::where('projek_id', $projek->id)
                ->where('tipe_id', $t->id)
                ->pluck('skema_pembayaran_id')
                ->unique()
                ->values()
                ->toArray();
            return [
                'id' => $t->id,
                'name' => $t->name,
                'luas_tanah' => $t->luas_tanah,
                'luas_bangunan' => $t->luas_bangunan,
                'jumlah_unit' => $t->jumlah_unit,
                'harga' => $t->harga,
                'jenis_pembayaran_ids' => $pembayaranIds,
            ];
        });

        $fasilitas = \App\Models\Fasilitas::where('projeks_id', $projek->id)->get()->map(function ($f) {
            return [
                'name' => $f->nama_fasilitas,
                'luas' => $f->luas_fasilitas,
            ];
        });

        $data = [
            'id' => $projek->id,
            'name' => $projek->name,
            'jumlah_kavling' => $projek->kavling_total,
            'alamat' => $projek->address,
            'tipe' => $tipeData,
            'fasilitas' => $fasilitas,
        ];

        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Projek $projek)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $projek = Projek::where('id', $id)->first();

        $projek->update([
            'name' => $request['name'],
            'kavling_total' => $request['jumlah_kavling'],
            'address' => $request['alamat'],
        ]);

        if($request['tipe']){
            Tipe::where('project_id', $projek->id)->delete();
            PembayaranProjeks::where('projek_id', $projek->id)->delete();

            foreach($request['tipe'] as $tipe){
                $tipeModel = Tipe::create([
                    'name' => $tipe['name'],
                    'luas_tanah' => $tipe['luas_tanah'],
                    'luas_bangunan' => $tipe['luas_bangunan'],
                    'jumlah_unit' => $tipe['jumlah_unit'],
                    'project_id' => $projek->id,
                    'harga' => $tipe['harga'],
                ]);

                if(isset($tipe['jenis_pembayaran_ids']) && is_array($tipe['jenis_pembayaran_ids'])){
                     foreach($tipe['jenis_pembayaran_ids'] as $pembayaranId){
                         PembayaranProjeks::create([
                             'projek_id' => $projek->id,
                             'tipe_id' => $tipeModel->id,
                             'skema_pembayaran_id' => $pembayaranId,
                         ]);
                     }
                 }
            }
        }

        if($request['fasilitas']){
            \App\Models\Fasilitas::where('projeks_id', $projek->id)->delete();
            foreach($request['fasilitas'] as $fasilitas){
                \App\Models\Fasilitas::create([
                    'nama_fasilitas' => $fasilitas['name'],
                    'luas_fasilitas' => $fasilitas['luas'],
                    'projeks_id' => $projek->id,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully',
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $projek = Projek::where('id', $id)->first();

        if (!$projek) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found',
            ], 404);
        }

        PembayaranProjeks::where('projek_id', $projek->id)->delete();
        Tipe::where('project_id', $projek->id)->delete();
        \App\Models\Fasilitas::where('projeks_id', $projek->id)->delete();

        $projek->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully',
        ], 201);
    }

    public function tipeByProjek($id)
    {
        $tipes = Tipe::where('project_id', $id)
            ->select('id', 'name', 'luas_tanah', 'luas_bangunan', 'jumlah_unit', 'harga')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($tipes);
    }
}
