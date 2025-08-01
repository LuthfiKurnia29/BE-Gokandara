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
        $search = $request->search;

        $authUser = auth()->user();
        $roles = $authUser->roles->pluck('role_id')->toArray();
        $authUserId = $authUser->id;

        $query = Chatting::with(['penerima.roles.role', 'pengirim.roles.role'])->when($search, function ($query) use ($search) {
            $query->where('pesan', 'like', "%$search%");
        });

        if (in_array(1, $roles)) {
            $query->where('user_pengirim_id', $authUserId);
            $chatData = $query->orderBy('created_at', 'desc')->get();

            // Group by pengirim dan created_at (format date time)
            $grouped = $chatData
                ->groupBy(function ($item) {
                    return $item->user_pengirim_id . '_' . $item->created_at->format('Y-m-d H:i:s');
                })
                ->map(function ($items, $key) {
                    $firstItem = $items->first();
                    return [
                        'pengirim' => $firstItem->pengirim->name ?? '-',
                        'created_at' => $firstItem->created_at->format('Y-m-d H:i:s'),
                        'pesan_list' => $items
                            ->map(function ($chat) {
                                return [
                                    'penerima' => $chat->penerima->name ?? '-',
                                    'pesan' => $chat->pesan,
                                ];
                            })
                            ->values(),
                    ];
                })
                ->values();

            return response()->json([
                'name' => $authUser->name,
                'pesan_dikirim_grouped' => $grouped,
            ]);
        } elseif (in_array(2, $roles)) {
            $chatData = $query
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
                ->orderBy('created_at', 'desc')
                ->get();

            // Group pesan diterima by pengirim dan created_at
            $pesanDiterima = $chatData
                ->where('user_penerima_id', $authUserId)
                ->groupBy(function ($item) {
                    return $item->user_pengirim_id . '_' . $item->created_at->format('Y-m-d H:i:s');
                })
                ->map(function ($items, $key) {
                    $firstItem = $items->first();
                    return [
                        'pengirim' => $firstItem->pengirim->name ?? '-',
                        'pengirim_role' => $firstItem->pengirim->roles->first()->role->name ?? '-',
                        'created_at' => $firstItem->created_at->format('Y-m-d H:i:s'),
                        'pesan_list' => $items
                            ->map(function ($chat) {
                                return [
                                    'pesan' => $chat->pesan,
                                ];
                            })
                            ->values(),
                    ];
                })
                ->values();

            // Group pesan dikirim by created_at (karena pengirim sama)
            $pesanDikirim = $chatData
                ->where('user_pengirim_id', $authUserId)
                ->groupBy(function ($item) {
                    return $item->created_at->format('Y-m-d H:i:s');
                })
                ->map(function ($items, $createdAt) {
                    return [
                        'pengirim' => $authUser->name,
                        'created_at' => $createdAt,
                        'pesan_list' => $items
                            ->map(function ($chat) {
                                return [
                                    'penerima' => $chat->penerima->name ?? '-',
                                    'pesan' => $chat->pesan,
                                ];
                            })
                            ->values(),
                    ];
                })
                ->values();

            return response()->json([
                'name' => $authUser->name,
                'pesan_diterima_grouped' => $pesanDiterima,
                'pesan_dikirim_grouped' => $pesanDikirim,
            ]);
        } elseif (in_array(3, $roles)) {
            $chatData = $query
                ->where('user_penerima_id', $authUserId)
                ->whereHas('pengirim.roles', function ($r) {
                    $r->whereIn('role_id', [1, 2]);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            // Group by pengirim dan created_at
            $pesanDiterima = $chatData
                ->groupBy(function ($item) {
                    return $item->user_pengirim_id . '_' . $item->created_at->format('Y-m-d H:i:s');
                })
                ->map(function ($items, $key) {
                    $firstItem = $items->first();
                    return [
                        'pengirim' => $firstItem->pengirim->name ?? '-',
                        'pengirim_role' => $firstItem->pengirim->roles->first()->role->name ?? '-',
                        'created_at' => $firstItem->created_at->format('Y-m-d H:i:s'),
                        'pesan_list' => $items
                            ->map(function ($chat) {
                                return [
                                    'pesan' => $chat->pesan,
                                ];
                            })
                            ->values(),
                    ];
                })
                ->values();

            return response()->json([
                'name' => $authUser->name,
                'pesan_diterima_grouped' => $pesanDiterima,
            ]);
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
