<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chatting;

class ChattingController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;

        $data = Chatting::with(['penerima', 'pengirim'])
            ->when($search, function ($query) use ($search) {
                $query->where('pesan', 'like', "%$search%");
            })
            ->when($request->id_pengirim, function ($query) use ($request) {
                $query->where('user_pengirim_id', auth()->user()->id);
            })
            ->when($request->id_penerima, function ($query) use ($request) {
                $query->orWhere('user_penerima_id', $request->id_penerima);
            })
            ->orderBy('id', 'desc')
            ->paginate($per);

        return response()->json($data);
    }

    public function store(Request $request) 
    {
        $validate = $request->validate([
            'user_penerima_id' => 'required',
            'pesan' => 'required',
        ]);

        $validate['user_pengirim_id'] = auth()->user()->id;

        Chatting::create($validate);

        return response()->json([
            'success' => true,
            'message' => 'Message sent successfully',
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $chat = Chatting::where('id', $id)->first();
        $validate = $request->validate([
            'pesan' => 'required',
        ]);

        $chat->update($validate);

        return response()->json([
            'success' => true,
            'message' => 'Chat updated successfully',
        ], 201);
    }

    public function destroy($id)
    {
        $chat = Chatting::findOrFail($id);
        $chat->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chat deleted successfully',
        ], 201);
    }
}
