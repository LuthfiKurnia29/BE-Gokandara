<?php

namespace App\Http\Controllers;

use App\Models\Blok;
use Illuminate\Http\Request;

class BlokController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bloks = Blok::paginate(10);
        return response()->json($bloks);
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

        Blok::create($validate);

        return response()->json([
            'success' => true,
            'message' => 'Blok created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Blok $blok)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blok $blok)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Blok $blok)
    {
        $validate = $request->validate([
            'Nama' => 'required|string|max:255',
        ]);

        $blok->update($validate);

        return response()->json([
            'success' => true,
            'message' => 'Blok updated successfully',
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blok $blok)
    {
        Blok::destroy($blok->id);

        return response()->json([
            'success' => true,
            'message' => 'Blok deleted successfully',
        ], 201);
    }
}
