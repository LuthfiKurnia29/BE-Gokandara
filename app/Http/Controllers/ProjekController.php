<?php

namespace App\Http\Controllers;

use App\Models\Projek;
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
                    $query->where('nama', 'like', "%$search%");
                }
            })
            ->orderBy('id', 'desc')
            ->paginate($per);

        return response()->json($data);
    }

    public function allProject(Request $request){
        $search = $request->search;
        $data = Projek::select('id', 'nama')
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
            'nama' => 'required|string|max:255'
        ]);

        Projek::create($validate);

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Projek $projek)
    {
        //
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
    public function update(Request $request, Projek $projek)
    {
        $validate = $request->validate([
            'nama' => 'required|string|max:255',
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
    public function destroy(Projek $projek)
    {
        Projek::destroy($projek->id);
        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully',
        ], 201);
    }
}
