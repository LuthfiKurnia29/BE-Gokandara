<?php

namespace App\Http\Controllers;

use App\Models\Tipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TipeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;

        $data = Tipe::where(function ($query) use ($search) {
                if ($search) {
                    $query->where('name', 'like', "%$search%");
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
            'name' => 'required|string|max:255'
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
            'name' => 'required|string|max:255',
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
