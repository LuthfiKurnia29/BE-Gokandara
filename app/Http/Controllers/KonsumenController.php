<?php

namespace App\Http\Controllers;

use App\Models\Konsumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KonsumenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;

        $data = Konsumen::with(['projek', 'prospek'])
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('Nama', 'like', "%$search%")
                        ->orWhere('Alamat', 'like', "%$search%")
                        ->orWhere('No_KTP', 'like', "%$search%")
                        ->orWhere('No_HP', 'like', "%$search%")
                        ->orWhere('Email', 'like', "%$search%")
                        ->orWhere('Pengalaman', 'like', "%$search%")
                        ->orWhere('Materi_Fu', 'like', "%$search%");
                }
            })
            ->orderBy('id', 'desc')
            ->paginate($per);

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
        $validate = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:konsumens',
            'no_hp' => 'required|string|max:15',
            'no_ktp' => 'required|string|max:16|unique:konsumens',
            'alamat' => 'required|string|max:255',
            'kesiapan_dana' => 'required|numeric|min:0',
            'pengalaman' => 'required|string|max:255',
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
