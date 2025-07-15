<?php

namespace App\Http\Controllers;

use App\Models\Konsumen;
use Illuminate\Http\Request;

class KonsumenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $konsumens = Konsumen::with('projek', 'prospek')->get();
        return response()->json($konsumens);
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
        $validate = $request->validate([
            'Nama' => 'required|string|max:255',
            'Email' => 'required|email|max:255|unique:konsumens',
            'No_HP' => 'required|string|max:15',
            'No_KTP' => 'required|string|max:16|unique:konsumens',
            'Alamat' => 'required|string|max:255',
            'Kesiapan_dana' => 'required|numeric|min:0',
            'Pengalaman' => 'required|string|max:255',
            'Materi_Fu' => 'required|string|max:255',
            'Tgl_Fu' => 'required|date',
            'Prospek_Id' => 'required',
            'Projek_Id' => 'required',
        ]);

        Konsumen::create($validate);

        return response()->json([
            'success' => true,
            'message' => 'Konsumen created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Konsumen $konsumen)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Konsumen $konsumen)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Konsumen $konsuman)
    {
        $validate = $request->validate([
            'Nama' => 'required|string|max:255',
            'Email' => 'required|email|max:255|unique:konsumens,Email,' . $konsuman->id,
            'No_KTP' => 'required|string|max:16|unique:konsumens,No_KTP,' . $konsuman->id,
            'No_HP' => 'required|string|max:15',
            'Alamat' => 'required|string|max:255',
            'Kesiapan_dana' => 'required|numeric|min:0',
            'Pengalaman' => 'required|string|max:255',
            'Materi_Fu' => 'required|string|max:255',
            'Tgl_Fu' => 'required|date',
            'Prospek_Id' => 'required',
            'Projek_Id' => 'required',
        ]);

        $konsuman->update($validate);

        return response()->json([
            'success' => true,
            'message' => 'Konsumen updated successfully',
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Konsumen $konsuman)
    {
        Konsumen::destroy($konsuman->id);

        return response()->json([
            'success' => true,
            'message' => 'Konsumen deleted successfully',
        ], 201);
    }
}
