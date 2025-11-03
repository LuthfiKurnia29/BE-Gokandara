<?php

namespace App\Http\Controllers;

use App\Models\FollowupMonitoring;
use App\Models\Konsumen;
use App\Models\User;
use App\Models\UserRole;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;


class KonsumenController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request) {
        $user = Auth::user();
        // var_dump($user); die;
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;
        $dateStart = $request->dateStart;
        $dateEnd = $request->dateEnd;
        $created_id = $request->created_id;
        $prospek_id = $request->prospek_id;
        $status = $request->status;
        // var_dump($search);
        $userRole = UserRole::with('role', 'user')->where('user_id', $user->id)->first();

        $data = Konsumen::with(['projek', 'prospek', 'createdBy', 'latestTransaksi'])
                ->where(function ($query) use ($search, $created_id, $user, $userRole, $dateStart, $dateEnd, $prospek_id, $status) {
                if ($userRole->role->name === 'Admin' && !$created_id) {
                    // Get All Sales under Admin
                    $query->orWhere('status_delete', 'pending');
                    if ($search) {
                    $query
                            ->where('name', 'like', "%$search%")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhere('ktp_number', 'like', "%$search%")
                            ->orWhere('phone', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%");
                    }
                }
                if ($created_id) {
                    $query->where('created_id', $created_id);
                    $query->orWhere('added_by', $created_id);
                    if ($search) {
                    $query
                            ->where('name', 'like', "$search")
                            ->orWhere('address', 'like', "%$search%")
                            ->orWhere('ktp_number', 'like', "%$search%")
                            ->orWhere('phone', 'like', "%$search%")
                            ->orWhere('email', 'like', "%$search%");
                    }
                } else {
                    $query->where('created_id', auth()->id());
                    $query->orWhere('added_by', auth()->id());
                }

                if ($search) {
                    $query
                        ->where('name', 'like', "%$search%")
                        ->orWhere('address', 'like', "%$search%")
                        ->orWhere('ktp_number', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                }
            })
            ->when($dateStart && $dateEnd, function ($query) use ($dateStart, $dateEnd) {
                $query->whereBetween('created_at', [$dateStart, $dateEnd]);
            })
            ->when($prospek_id, function ($query) use ($prospek_id) {
                $query->where('prospek_id', $prospek_id);
            })
            ->when($status, function ($query) use ($status) {
                $query->whereHas('latestTransaksi', function ($q) use ($status) {
                    $q->where('status', $status);
                });
            })
            ->orderBy('id', 'desc')
            ->paginate($per);

        return response()->json($data);
    }

    /**
     * Select list konsumen by created_id
     */
    public function getKonsumenByCreatedId(string $createdId) {
        $data = Konsumen::where('created_id', $createdId)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'success get konsumen by created_id',
            'data' => $data
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {
        $user = Auth::user();
        $userRoles = $user->roles->pluck('role_id')->toArray();
        $isMitra = in_array(4, $userRoles);

        $availKonsumen = Konsumen::where('phone', $request->phone)->where('project_id', $request->project_id)->first();

        if ($availKonsumen) {
            Notifikasi::create([
                'penerima_id' => 1,
                'konsumen_id' => $availKonsumen->id,
                'chat_id' => null,
                'jenis_notifikasi' => 'konsumen',
                'is_read' => false,
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Konsumen dengan Nomer Telepon ini sudah ada di Projek yang dipilih.',
                ],
                400,
            );
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('konsumens')->where(function ($query) use ($request) {
                    return $query->where('email', $request->email);
                }),
            ],
            'phone' => 'required|string|max:20',
            'ktp_number' => [
                'nullable',
                'string',
                'max:16',
                Rule::unique('konsumens')->where(function ($query) use ($request) {
                    return $query->where('ktp_number', $request->ktp_number);
                }),
            ],
            'address' => 'required|string|max:255',
            'project_id' => 'required',
            'refrensi_id' => 'required',
            'prospek_id' => 'required',
            'kesiapan_dana' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'pengalaman' => 'nullable|string|max:255',
            'materi_fu_1' => 'required|string',
            'tgl_fu_1' => 'required|string',
            'materi_fu_2' => 'required|string',
            'tgl_fu_2' => 'required|string',
            'created_id' => 'nullable|exists:users,id',
        ];

        $messages = [
            'email.unique' => 'Email sudah terdaftar.',
            'ktp_number.unique' => 'Nomor KTP sudah terdaftar.',
        ];

        if ($messages) {
            foreach ($messages as $key => $msg) {
                if ($request->has($key) && Konsumen::where($key, $request->$key)->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => $msg,
                    ], 400);
                }
            }
        }

        if ($isMitra) {
            $rules['gambar'] = 'required|image|max:2048';
        } else {
            $rules['gambar'] = 'nullable|image|max:2048';
        }

        $validate = $request->validate($rules);

        $validate['tgl_fu_1'] = Carbon::parse($validate['tgl_fu_1'])->format('Y-m-d H:i:s');
        $validate['tgl_fu_2'] = Carbon::parse($validate['tgl_fu_2'])->format('Y-m-d H:i:s');
        $validate['added_by'] = $user->id;

        if ($request->has('created_id')) {
            $validate['created_id'] = $request->created_id;
        } else {
            $validate['created_id'] = auth()->user()->id;
        }
        $validate['updated_id'] = auth()->user()->id;

        if ($request->hasFile('gambar')) {
            $validate['gambar'] = $request->file('gambar')->store('gambarKonsumen', 'public');
        } else {
            unset($validate['gambar']);
        }

        $data = Konsumen::create($validate);

        // Create a follow-up entry for the new konsumen
        $followupData1 = [
            'followup_date' => $validate['tgl_fu_1'],
            'followup_note' => $validate['materi_fu_1'],
            'followup_result' => null,
            'konsumen_id' => $data->id,
            'sales_id' => $user->id,
            'prospek_id' => $validate['prospek_id'],
        ];
        FollowupMonitoring::create($followupData1);

        $followupData2 = [
            'followup_date' => $validate['tgl_fu_2'],
            'followup_note' => $validate['materi_fu_2'],
            'followup_result' => null,
            'konsumen_id' => $data->id,
            'sales_id' => $user->id,
            'prospek_id' => $validate['prospek_id'],
        ];
        FollowupMonitoring::create($followupData2);

        return response()->json(
            [
                'success' => true,
                'message' => 'Konsumen created successfully',
            ],
            201,
        );
    }

    public function allKonsumen(Request $request) {
        $search = $request->search;
        $data = Konsumen::select('id', 'name')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            })
            ->when(!auth()->user()->hasRole('Admin'), function ($query) {
                $query->where('created_id', auth()->user()->id);
            })
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($data);
    }

    public function allKonsumenBySales(Request $request) {
        $user = Auth::user();
        $search = $request->search;
        $data = Konsumen::leftJoin('users', 'users.id', '=', 'konsumens.created_id')
            ->where('added_by', auth()->user()->id)
            ->orderBy('konsumens.id', 'desc')
            ->select('konsumens.*', 'users.name as assign_name')
            ->get();

        return response()->json($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {
        $data = Konsumen::with(['projek', 'prospek'])
            ->where('id', $id)
            ->first();
        return response()->json($data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Konsumen $konsumen) {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {
        $user = Auth::user();
        $konsumen = Konsumen::where('id', $id)->first();

        $userRoles = $user->roles->pluck('role_id')->toArray();
        $isMitra = in_array(4, $userRoles);

        $validate = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['nullable', 'email', 'max:255', Rule::unique('konsumens')->ignore($konsumen->id)],
            'phone' => 'required|string|max:20',
            'ktp_number' => ['nullable', 'string', 'max:16'],
            'address' => 'required|string|max:255',
            'project_id' => 'required',
            'refrensi_id' => 'required',
            'prospek_id' => 'required',
            'kesiapan_dana' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'pengalaman' => 'nullable|string|max:255',
            'materi_fu_1' => 'required|string',
            'tgl_fu_1' => 'required|string',
            'materi_fu_2' => 'required|string',
            'tgl_fu_2' => 'required|string',
            'gambar' => [Rule::when($isMitra && !$konsumen->gambar, ['required', 'image', 'max:2048'], ['nullable', 'image', 'max:2048'])],
            'created_id' => 'nullable|exists:users,id',
        ]);

        $validate['tgl_fu_1'] = Carbon::parse($validate['tgl_fu_1'])->format('Y-m-d H:i:s');
        $validate['tgl_fu_2'] = Carbon::parse($validate['tgl_fu_2'])->format('Y-m-d H:i:s');
        $validate['added_by'] = $user->id;

        if ($request->has('created_id')) {
            $validate['created_id'] = $request->created_id;
        } else {
            $validate['created_id'] = auth()->user()->id;
        }
        $validate['updated_id'] = auth()->user()->id;

        if ($request->hasFile('gambar')) {
            if ($konsumen->gambar && Storage::disk('public')->exists($konsumen->gambar)) {
                Storage::disk('public')->delete($konsumen->gambar);
            }
            $validate['gambar'] = $request->file('gambar')->store('gambarKonsumen', 'public');
        } else {
            $validate['gambar'] = $konsumen->gambar;
        }

        $konsumen->update($validate);

        return response()->json(
            [
                'success' => true,
                'message' => 'Konsumen updated successfully',
            ],
            200,
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id) {
        $authUser = auth()->user();
        $roles = $authUser->roles->pluck('role_id')->toArray();

        if (in_array(1, $roles) || in_array(2, $roles) || in_array(4, $roles)) {
            $konsumen = Konsumen::findOrFail($id);
            if ($konsumen->gambar && file_exists(storage_path($konsumen->gambar))) {
                unlink(storage_path($konsumen->gambar));
            }
            Konsumen::where('id', $id)->delete();
            FollowupMonitoring::where('konsumen_id', $id)->delete();

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Konsumen deleted successfully',
                ],
                201,
            );
        } else {
            $konsumen = Konsumen::where('id', $id)->first();
            $konsumen->update(['status_delete' => 'pending']);

            return response()->json(
                [
                    'success' => true,
                    'message' => 'The application to delete the konsumen has been successfully submitted.',
                ],
                201,
            );
        }
    }

    public function approveDeleteAdmin($id) {
        $konsumen = Konsumen::where('id', $id)->first();
        $konsumen->update(['status_delete' => 'deleted']);
        $konsumen->delete();
        FollowupMonitoring::where('konsumen_id', $id)->delete();

        return response()->json(
            [
                'success' => true,
                'message' => 'Konsumen deleted successfully',
            ],
            201,
        );
    }

    public function konsumenBySales(Request $request) {
        $user = Auth::user();
        $data = Konsumen::where('added_by', $user->id)
            ->with(['projek', 'prospek'])
            ->orderBy('id', 'desc')
            ->get();
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Konsumen retrieved successfully',
        ]);
    }

    public function konsumenBySupervisor(Request $request) {
        $user = Auth::user();
        $sales = User::where('parent_id', $user->id)->pluck('id');

        $data = Konsumen::where(function ($q) use ($user, $sales) {
            $q->where('added_by', $user->id)->orWhereIn('added_by', $sales);
        })
            ->with(['projek', 'prospek'])
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Konsumen retrieved successfully',
        ]);
    }

    /**
     * Export konsumen to CSV with same filters as index
     */
    public function export(Request $request) {
        $user = Auth::user();
        $userRole = UserRole::with('role', 'user')->where('user_id', $user->id)->first();

        // Get filters from request
        $search = $request->search;
        $dateStart = $request->dateStart;
        $dateEnd = $request->dateEnd;
        $created_id = $request->created_id;
        $prospek_id = $request->prospek_id;
        $status = $request->status;

        // Set file name
        $fileName = 'konsumen_' . date('Y-m-d_His') . '.csv';

        // Create response with streamed content
        return new StreamedResponse(
            function () use ($search, $dateStart, $dateEnd, $created_id, $prospek_id, $status, $user, $userRole) {
                // Open output stream
                $handle = fopen('php://output', 'w');

                // Add BOM for UTF-8 to ensure proper encoding in Excel
                fwrite($handle, "\xEF\xBB\xBF");

                // Define headers
                $headers = [
                    'ID',
                    'Nama',
                    'Email',
                    'Telepon',
                    'No. KTP',
                    'Alamat',
                    'Projek',
                    'Prospek',
                    'Kesiapan Dana',
                    'Pengalaman',
                    'Deskripsi',
                    'Status Transaksi',
                    'Dibuat Oleh',
                    'Tanggal Dibuat',
                    'Tanggal Diupdate'
                ];

                // Write headers
                fputcsv($handle, $headers);

                // Build base query
                $baseQuery = Konsumen::with(['projek', 'prospek', 'createdBy', 'latestTransaksi'])
                    ->where(function ($query) use ($search, $created_id, $user, $userRole) {
                        if ($created_id) {
                            $query->where('created_id', $created_id);
                            $query->orWhere('added_by', $created_id);
                        } else {
                            $query->where('created_id', $user->id);
                            $query->orWhere('added_by', $user->id);
                        }

                        if ($userRole->role->name === 'Admin' && !$created_id) {
                            // Get All Sales under Admin
                            $query->orWhere('status_delete', 'pending');
                        }

                        if ($search) {
                            $query
                                ->where('name', 'like', "%$search%")
                                ->orWhere('address', 'like', "%$search%")
                                ->orWhere('ktp_number', 'like', "%$search%")
                                ->orWhere('phone', 'like', "%$search%")
                                ->orWhere('email', 'like', "%$search%");
                        }
                    })
                    ->when($dateStart && $dateEnd, function ($query) use ($dateStart, $dateEnd) {
                        $query->whereBetween('created_at', [$dateStart, $dateEnd]);
                    })
                    ->when($prospek_id, function ($query) use ($prospek_id) {
                        $query->where('prospek_id', $prospek_id);
                    })
                    ->when($status, function ($query) use ($status) {
                        $query->whereHas('latestTransaksi', function ($q) use ($status) {
                            $q->where('status', $status);
                        });
                    })
                    ->orderBy('id', 'desc');

                // Process data in chunks
                $baseQuery->chunk(100, function ($konsumenChunk) use ($handle) {
                    foreach ($konsumenChunk as $konsumen) {
                        $row = [
                            $konsumen->id,
                            $konsumen->name,
                            $konsumen->email ?? '-',
                            $konsumen->phone,
                            $konsumen->ktp_number ?? '-',
                            $konsumen->address,
                            $konsumen->projek->name ?? '-',
                            $konsumen->prospek->name ?? '-',
                            'Rp ' . number_format($konsumen->kesiapan_dana ?? 0, 0, ',', '.'),
                            $konsumen->pengalaman ?? '-',
                            $konsumen->description ?? '-',
                            $konsumen->latestTransaksi->status ?? 'Belum Ada Transaksi',
                            $konsumen->createdBy->name ?? '-',
                            $konsumen->created_at->format('d-m-Y H:i:s'),
                            $konsumen->updated_at->format('d-m-Y H:i:s')
                        ];

                        fputcsv($handle, $row);
                    }

                    // Clear memory after each chunk
                    unset($konsumenChunk);
                    gc_collect_cycles();
                });

                // Close handle
                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }

    /**
     * Export konsumen to CSV with same filters as index (Memory Optimized)
     */
    public function exportOptimized(Request $request) {
        $user = Auth::user();
        $userRole = UserRole::with('role', 'user')->where('user_id', $user->id)->first();

        // Get filters from request
        $search = $request->search;
        $dateStart = $request->dateStart;
        $dateEnd = $request->dateEnd;
        $created_id = $request->created_id;
        $prospek_id = $request->prospek_id;
        $status = $request->status;

        // Set file name
        $fileName = 'konsumen_' . date('Y-m-d_His') . '.csv';

        // Create response with streamed content
        return new StreamedResponse(
            function () use ($search, $dateStart, $dateEnd, $created_id, $prospek_id, $status, $user, $userRole) {
                // Open output stream
                $handle = fopen('php://output', 'w');

                // Add BOM for UTF-8 to ensure proper encoding in Excel
                fwrite($handle, "\xEF\xBB\xBF");

                // Define headers
                $headers = [
                    'ID',
                    'Nama',
                    'Email',
                    'Telepon',
                    'No. KTP',
                    'Alamat',
                    'Projek',
                    'Prospek',
                    'Kesiapan Dana',
                    'Pengalaman',
                    'Deskripsi',
                    'Status Transaksi',
                    'Dibuat Oleh',
                    'Tanggal Dibuat',
                    'Tanggal Diupdate'
                ];

                // Write headers
                fputcsv($handle, $headers);

                // Build base query without eager loading to reduce memory usage
                $baseQuery = Konsumen::select(
                    'id',
                    'name',
                    'email',
                    'phone',
                    'ktp_number',
                    'address',
                    'project_id',
                    'prospek_id',
                    'kesiapan_dana',
                    'pengalaman',
                    'description',
                    'created_id',
                    'created_at',
                    'updated_at'
                )
                    ->where(function ($query) use ($search, $created_id, $user, $userRole) {
                        if ($created_id) {
                            $query->where('created_id', $created_id);
                            $query->orWhere('added_by', $created_id);
                        } else {
                            $query->where('created_id', $user->id);
                            $query->orWhere('added_by', $user->id);
                        }

                        if ($userRole->role->name === 'Admin' && !$created_id) {
                            $query->orWhere('status_delete', 'pending');
                        }

                        if ($search) {
                            $query
                                ->where('name', 'like', "%$search%")
                                ->orWhere('address', 'like', "%$search%")
                                ->orWhere('ktp_number', 'like', "%$search%")
                                ->orWhere('phone', 'like', "%$search%")
                                ->orWhere('email', 'like', "%$search%");
                        }
                    })
                    ->when($dateStart && $dateEnd, function ($query) use ($dateStart, $dateEnd) {
                        $query->whereBetween('created_at', [$dateStart, $dateEnd]);
                    })
                    ->when($prospek_id, function ($query) use ($prospek_id) {
                        $query->where('prospek_id', $prospek_id);
                    })
                    ->when($status, function ($query) use ($status) {
                        $query->whereHas('latestTransaksi', function ($q) use ($status) {
                            $q->where('status', $status);
                        });
                    })
                    ->orderBy('id', 'desc');

                // Use cursor for memory-efficient processing
                $baseQuery->cursor()->each(function ($konsumen) use ($handle) {
                    // Load relationships only when needed
                    $projek = \App\Models\Projek::find($konsumen->project_id);
                    $prospek = \App\Models\Prospek::find($konsumen->prospek_id);
                    $createdBy = \App\Models\User::find($konsumen->created_id);
                    $latestTransaksi = \App\Models\Transaksi::where('konsumen_id', $konsumen->id)
                        ->orderBy('id', 'desc')->first();

                    $row = [
                        $konsumen->id,
                        $konsumen->name,
                        $konsumen->email ?? '-',
                        $konsumen->phone,
                        $konsumen->ktp_number ?? '-',
                        $konsumen->address,
                        $projek->name ?? '-',
                        $prospek->name ?? '-',
                        'Rp ' . number_format($konsumen->kesiapan_dana ?? 0, 0, ',', '.'),
                        $konsumen->pengalaman ?? '-',
                        $konsumen->description ?? '-',
                        $latestTransaksi->status ?? 'Belum Ada Transaksi',
                        $createdBy->name ?? '-',
                        $konsumen->created_at->format('d-m-Y H:i:s'),
                        $konsumen->updated_at->format('d-m-Y H:i:s')
                    ];

                    fputcsv($handle, $row);

                    // Force garbage collection every 50 records
                    static $counter = 0;
                    if (++$counter % 50 === 0) {
                        gc_collect_cycles();
                    }
                });

                // Close handle
                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'max-age=0',
            ]
        );
    }
}
