<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notifikasi;

class NotifikasiController extends Controller
{
    public function index(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;

        $data = Notifikasi::with('konsumen', 'chatting')
            ->where('penerima_id', auth()->user()->id)
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('jenis_notifikasi', 'like', "%$search%");
                }
            })
            ->orderBy('id', 'desc')
            ->paginate($per);

        return response()->json($data);
    }

    public function indexUnread(Request $request)
    {
        $per = $request->per ?? 10;
        $page = $request->page ?? 1;
        $search = $request->search;

        $data = Notifikasi::with('konsumen', 'chatting')
            ->where('penerima_id', auth()->user()->id)
            ->where('is_read', false)
            ->where(function ($query) use ($search) {
                if ($search) {
                    $query->where('jenis_notifikasi', 'like', "%$search%");
                }
            })
            ->orderBy('id', 'desc')
            ->paginate($per);

        return response()->json($data);
    }

    public function read($id)
    {
        $notifikasi = Notifikasi::findOrFail($id);
        $notifikasi->update(['is_read' => true]);

        return response()->json(
            [
                'success' => true,
                'message' => 'Notifikasi marked as read',
            ],
            200,
        );
    }

    public function readAll($id)
    {
        $notifikasi = Notifikasi::where('penerima_id', auth()->user()->id)->update(['is_read' => true]);

        return response()->json(
            [
                'success' => true,
                'message' => 'Notifikasi marked as read',
            ],
            200,
        );
    }

    public function count() 
    {
        $countNotif = Notifikasi::where('penerima_id', auth()->user()->id)
            ->where('is_read', false)
            ->count();

        return response()->json($countNotif);
    }

    public function destroy($id)
    {
        $notifikasi = Notifikasi::findOrFail($id);
        $notifikasi->delete();

        return response()->json(
            [
                'success' => true,
                'message' => 'Notifikasi deleted successfully',
            ],
            201,
        );
    }
}
