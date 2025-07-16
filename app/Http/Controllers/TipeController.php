<?php

namespace App\Http\Controllers;

use App\Models\Tipe;
use Illuminate\Http\Request;

class TipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tipes = Tipe::paginate(10);
        return response()->json($tipes);
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
            'Nama' => 'required|string|max:255'
        ]);

        Tipe::create($validate);

        return response()->json([
            'success' => true,
            'message' => 'Tipe created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(tipe $tipe)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(tipe $tipe)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, tipe $tipe)
    {
        $validate = $request->validate([
            'Nama' => 'required|string|max:255',
        ]);

        $tipe->update($validate);

        return response()->json([
            'success' => true,
            'message' => 'Tipe updated successfully',
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(tipe $tipe)
    {
        Tipe::destroy($tipe->id);

        return response()->json([
            'success' => true,
            'message' => 'Tipe deleted successfully',
        ], 201);
    }
}
