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
        $data = Projek::select('id', 'name')
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
        //
        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'kavling_total' => 'required|integer',
            'address' => 'required|string|max:255',
        ]);

        $projek = Projek::create($validate);

        if($request['tipes']){
            foreach($request['tipes'] as $tipe){
                $projek = Tipe::create([
                    'nama_tipe' => $tipe['nama_tipe'],
                    'luas_tanah' => $tipe['luas_tanah'],
                    'luas_bangunan' => $tipe['luas_bangunan'],
                    'jumlah_unit' => $tipe['jumlah_unit'],
                    'projeks_id' => $projek->id,
                    'harga' => $tipe['harga'],
                ]);

                if(isset($tipe['jenis_pembayaran']) && is_array($tipe['jenis_pembayaran'])){
                    foreach($tipe['jenis_pembayaran'] as $pembayaranId){
                        PembayaranProjeks::create([
                            'projek_id' => $projek->id,
                            'skema_pembayaran_id' => $pembayaranId,
                        ]);
                    }
                }
            }
        }

        if($request['fasilitas']){
            foreach($request['fasilitas'] as $fasilitas){
                \App\Models\Fasilitas::create([
                    'nama_fasilitas' => $fasilitas['nama_fasilitas'],
                    'luas_fasilitas' => $fasilitas['luas_fasilitas'],
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
        $data = Projek::where('id', $id)->first();

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
        $validate = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $projek->update($validate);

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
        Projek::destroy($id);
        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully',
        ], 201);
    }
}
