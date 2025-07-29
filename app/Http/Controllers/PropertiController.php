<?php

namespace App\Http\Controllers;

use App\Models\Properti;
use App\Models\Properti_Gambar;
use Illuminate\Http\Request;

class PropertiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;

        $data = Properti::with(['propertiGambar', 'projek'])->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('luas_bangunan', 'like', "%$search%")
                          ->orWhere('luas_tanah', 'like', "%$search%")
                          ->orWhere('kelebihan', 'like', "%$search%")
                          ->orWhere('lokasi', 'like', "%$search%")
                          ->orWhere('harga', 'like', "%$search%");
                }
            })
            ->orderBy('id', 'desc')
            ->paginate($per);

        return response()->json($data);
    }

    public function allProperti(Request $request)
    {
        $search = $request->search;
        $data = Properti::select('id', 'luas_bangunan', 'luas_tanah', 'kelebihan', 'lokasi', 'harga')
                ->when($search, function ($query) use ($search) {
                    $query->where('luas_bangunan', 'like', "%$search%")
                          ->orWhere('luas_tanah', 'like', "%$search%")
                          ->orWhere('kelebihan', 'like', "%$search%")
                          ->orWhere('lokasi', 'like', "%$search%")
                          ->orWhere('harga', 'like', "%$search%");
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
        $validate = $request->validate([
            'project_id' => 'required',
            'luas_bangunan' => 'required|string',
            'luas_tanah' => 'required|string',
            'kelebihan' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'harga' => 'required|numeric',
            'properti__gambars' => 'required|array',
        ]);

        $properti = Properti::create($validate);

        foreach ($validate['properti__gambars'] as $gambar) {
            $gambarData = [];
            $gambarData['properti_id'] = $properti->id;
            $gambarData['image'] = $gambar->store('public', 'properti_images');
            Properti_Gambar::create($gambar);
        }

        return response()->json([
            'success' => true,
            'message' => 'Properti created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $data = Properti::where('id', $id)->first();

        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $properti = Properti::where('id', $id)->first();
        $validate = $request->validate([
            'project_id' => 'required',
            'luas_bangunan' => 'required|numeric',
            'luas_tanah' => 'required|numeric',
            'kelebihan' => 'required|string|max:255',
            'lokasi' => 'required|string|max:255',
            'harga' => 'required|numeric',
            'properti__gambars' => 'nullable|array',
        ]);

        $properti->update($validate);

        if ($request->hasFile('properti__gambars')) {
            Properti_Gambar::where('properti_id', $properti->id)->delete();

            foreach ($request->file('properti__gambars') as $gambar) {
            $gambarData = [
                'properti_id' => $properti->id,
                'image' => $gambar->store('public', 'properti_images'),
            ];
            Properti_Gambar::create($gambarData);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Properti updated successfully',
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $properti = Properti::findOrFail($id);
        if ($properti->propertiGambar) {
            foreach ($properti->propertiGambar as $gambar) {
                if ($gambar->image) {
                    unlink(storage_path('properti_images/' . $gambar->image));
                }
            }
        }
        Properti_Gambar::where('properti_id', $properti->id)->delete();
        $properti->delete();

        return response()->json([
            'success' => true,
            'message' => 'Properti deleted successfully',
        ], 201);
    }
}
