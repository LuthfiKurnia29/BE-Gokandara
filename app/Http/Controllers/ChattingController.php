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

        $authUserId = auth()->user()->id;
        $lawanBicaraId = $request->id_user;

        $data = Chatting::with(['penerima', 'pengirim'])
            ->when($search, function ($query) use ($search) {
                $query->where('pesan', 'like', "%$search%");
            })
            ->where(function ($query) use ($authUserId, $lawanBicaraId) {
                $query
                    ->where(function ($q) use ($authUserId, $lawanBicaraId) {
                        $q->where('user_pengirim_id', $authUserId)->where('user_penerima_id', $lawanBicaraId);
                    })
                    ->orWhere(function ($q) use ($authUserId, $lawanBicaraId) {
                        $q->where('user_pengirim_id', $lawanBicaraId)->where('user_penerima_id', $authUserId);
                    });
            })
            ->orderBy('id', 'desc')
            ->paginate($per);

        return response()->json($data);
    }

    public function lastChatting(Request $request)
    {
        $per = $request->per ?? 10;
        $search = $request->search;

        // Ambil ID chat terakhir dari setiap user_pengirim_id
        $latestMessageIds = Chatting::selectRaw('MAX(id) as id')->groupBy('user_pengirim_id');

        // Ambil chat dengan relasi user, lalu filter berdasarkan user (searchable)
        $data = Chatting::with('pengirim')
            ->whereIn('id', $latestMessageIds)
            ->whereHas('pengirim', function ($query) use ($search) {
                if ($search) {
                    $query->where('pesan', 'like', "%$search%");
                }
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

        return response()->json(
            [
                'success' => true,
                'message' => 'Message sent successfully',
            ],
            201,
        );
    }

    public function update(Request $request, $id)
    {
        $chat = Chatting::where('id', $id)->first();
        $validate = $request->validate([
            'pesan' => 'required',
        ]);

        $chat->update($validate);

        return response()->json(
            [
                'success' => true,
                'message' => 'Chat updated successfully',
            ],
            201,
        );
    }

    public function destroy($id)
    {
        $chat = Chatting::findOrFail($id);
        $chat->delete();

        return response()->json(
            [
                'success' => true,
                'message' => 'Chat deleted successfully',
            ],
            201,
        );
    }
}
