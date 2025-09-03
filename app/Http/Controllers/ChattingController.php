<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chatting;
use App\Models\User;
use App\Models\Notifikasi;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ChattingController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;

        $authUser = auth()->user();
        $roles = $authUser->roles->pluck('role_id')->toArray();
        $authUserId = $authUser->id;

        if (in_array(1, $roles)) {
            $latestPerCode = Chatting::select('code', DB::raw('MAX(id) as latest_id'))
                ->when($search, function ($query) use ($search) {
                    $query->where('pesan', 'like', "%$search%");
                })
                ->where('user_pengirim_id', $authUserId)
                ->groupBy('code');

            $chattingPaginated = Chatting::with(['pengirim.roles.role'])
                ->joinSub($latestPerCode, 'latest_chat', function ($join) {
                    $join->on('chattings.id', '=', 'latest_chat.latest_id');
                })
                ->orderByDesc('chattings.created_at')
                ->select('chattings.*')
                ->paginate($per);

            $codes = $chattingPaginated->pluck('code')->toArray();

            $penerimas = Chatting::whereIn('code', $codes)
                ->with('penerima.roles.role')
                ->get()
                ->groupBy('code')
                ->map(function ($chats) {
                    return $chats->pluck('penerima')->unique('id')->values();
                });

            $data = $chattingPaginated->getCollection()->map(function ($item) use ($penerimas) {
                $item->penerima = $penerimas[$item->code] ?? collect();
                unset($item->user_penerima_id);
                return $item;
            });

            $chattingPaginated->setCollection($data);

            return response()->json($chattingPaginated);
        } else if (in_array(2, $roles)) {
            $latestPerCode = Chatting::select('code', DB::raw('MAX(id) as latest_id'))
                ->where(function ($q) use ($authUserId) {
                    $q->where(function ($sub) use ($authUserId) {
                        $sub->whereHas('pengirim.roles', function ($r) {
                            $r->where('role_id', 1);
                        })->where('user_penerima_id', $authUserId);
                    })->orWhere(function ($sub) use ($authUserId) {
                        $sub->where('user_pengirim_id', $authUserId)->whereHas('penerima.roles', function ($r) {
                            $r->where('role_id', 3);
                        });
                    });
                })
                ->when($search, function ($query) use ($search) {
                    $query->where('pesan', 'like', "%$search%");
                })
                ->groupBy('code');

            $data = Chatting::with(['penerima.roles.role', 'pengirim.roles.role'])
                ->joinSub($latestPerCode, 'latest_chat', function ($join) {
                    $join->on('chattings.id', '=', 'latest_chat.latest_id');
                })
                ->orderByDesc('chattings.created_at')
                ->select('chattings.*')
                ->get();

            $allChats = Chatting::with(['pengirim.roles.role', 'penerima.roles.role'])
                ->whereIn('code', $data->pluck('code'))
                ->get()
                ->groupBy('code');

            $data->transform(function ($item) use ($allChats) {
                $allGrouped = $allChats[$item->code] ?? collect();

                $item->penerima_group = $allGrouped
                    ->map(function ($chat) {
                        return optional($chat->penerima->roles->first()->role)->name;
                    })
                    ->unique()
                    ->values();

                $item->penerima = $allGrouped
                    ->map(function ($chat) {
                        return $chat->penerima;
                    })
                    ->unique('id')
                    ->values();

                $item->pengirim_group = $allGrouped
                    ->map(function ($chat) {
                        return optional($chat->pengirim->roles->first()->role)->name;
                    })
                    ->unique()
                    ->values();

                $item->pengirim = $allGrouped
                    ->map(function ($chat) {
                        return $chat->pengirim;
                    })
                    ->unique('id')
                    ->values();

                return $item;
            });

            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $perPage = $per ?? 10;
            $currentItems = $data->forPage($currentPage, $perPage)->values();

            $paginated = new LengthAwarePaginator($currentItems, $data->count(), $perPage, $currentPage, ['path' => request()->url(), 'query' => request()->query()]);

            return response()->json($paginated);
        } elseif (in_array(3, $roles)) {
            $latestPerSenderCode = Chatting::select(DB::raw('CONCAT(user_pengirim_id, "_", code) as sender_code'), DB::raw('MAX(id) as latest_id'))
                ->where('user_penerima_id', $authUserId)
                ->whereHas('pengirim.roles', function ($r) {
                    $r->whereIn('role_id', [1, 2]);
                })
                ->when($search, function ($query) use ($search) {
                    $query->where('pesan', 'like', "%$search%");
                })
                ->groupBy('sender_code');

            $data = Chatting::with(['penerima.roles.role', 'pengirim.roles.role'])
                ->joinSub($latestPerSenderCode, 'latest_chat', function ($join) {
                    $join->on('chattings.id', '=', 'latest_chat.latest_id');
                })
                ->orderByDesc('chattings.created_at')
                ->select('chattings.*')
                ->paginate($per);

            return response()->json($data);
        }

        return response()->json(['message' => 'Unauthorized or role not recognized.'], 403);
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
            'roles',
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
        $validate['code'] = bin2hex(random_bytes(8));

        foreach ($validate['user_penerima_id'] as $penerimaId) {
            $validate['user_penerima_id'] = $penerimaId;
            $validate['file'] = $request->file('file') ? $request->file('file')->store('chat_files', 'public') : null;
            $chat = Chatting::create($validate);

            Notifikasi::create([
                'penerima_id' => $validate['user_penerima_id'],
                'konsumen_id' => null,
                'chat_id' => $chat->id,
                'jenis_notifikasi' => 'chat',
                'is_read' => false
            ]);
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

    public function destroy(Request $request)
    {
        foreach ($request->id as $id) {
            $chat = Chatting::findOrFail($id);
            if ($chat->file && file_exists(storage_path($chat->file))) {
                unlink(storage_path('chat_files/' . $chat->file));
            }
            $chat->delete();
        }

        return response()->json(
            [
                'success' => true,
                'message' => 'Chat deleted successfully',
            ],
            201,
        );
    }
}
