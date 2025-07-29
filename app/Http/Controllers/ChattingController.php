<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chatting;
use App\Models\User;

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

        $users = User::with([
            'chatDikirim' => function ($q) {
                $q->latest()->limit(1);
            },
            'chatDiterima' => function ($q) {
                $q->latest()->limit(1);
            },
            'roles'
        ])
            ->whereNot('id', auth()->user()->id)
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%$search%")->orWhere('email', 'like', "%$search%");
            })
            ->orderBy('id', 'desc')
            ->paginate($per);

        $users->getCollection()->transform(function ($user) {
            $lastSent = $user->chatDikirim->first();
            $lastReceived = $user->chatDiterima->first();

            if ($lastSent && $lastReceived) {
                $user->last_message = $lastSent->created_at > $lastReceived->created_at ? $lastSent : $lastReceived;
            } elseif ($lastSent) {
                $user->last_message = $lastSent;
            } elseif ($lastReceived) {
                $user->last_message = $lastReceived;
            } else {
                $user->last_message = null;
            }

            $userArray = $user->toArray();
            unset($userArray['chat_dikirim'], $userArray['chat_diterima']);

            return $userArray;
        });

        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validate = $request->validate([
            'user_penerima_id' => 'required|array',
            'pesan' => 'required',
            'file' => 'nullable',
        ]);

        $validate['user_pengirim_id'] = auth()->user()->id;

        foreach ($validate['user_penerima_id'] as $penerimaId) {
            $validate['user_penerima_id'] = $penerimaId;
            $validate['file'] = $request->file('file') ? $request->file('file')->store('public', 'chat_files') : null;
            Chatting::create($validate);
        }

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
        if ($chat->file) {
            unlink(storage_path('chat_files/' . $chat->file));
        }
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
