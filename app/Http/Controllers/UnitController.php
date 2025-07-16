<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $units = Unit::paginate(10);
        return response()->json($units);
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

        Unit::create($validate);

        return response()->json([
            'success' => true,
            'message' => 'Unit created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Unit $unit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Unit $unit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Unit $unit)
    {
        $validate = $request->validate([
            'Nama' => 'required|string|max:255',
        ]);

        $unit->update($validate);

        return response()->json([
            'success' => true,
            'message' => 'Unit updated successfully',
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit)
    {
        Unit::destroy($unit->id);

        return response()->json([
            'success' => true,
            'message' => 'Unit deleted successfully',
        ], 201);
    }
}
