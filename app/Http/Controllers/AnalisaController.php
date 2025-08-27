<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Konsumen;
use App\Models\FollowupMonitoring;
use App\Models\Transaksi;
use App\Models\Target;

class AnalisaController extends Controller
{
    public function getNewKonsumen(Request $request)
    {
        $sales = $request->created_id;

        $data = Konsumen::where(function ($query) use ($sales) {
            if ($sales) {
                $query->where('created_id', $sales);
            }
        })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($data);
    }

    public function getFollowup(Request $request)
    {
        $sales = $request->created_id;
        $waktu = $request->waktu; // today, tomorrow
        $data = FollowupMonitoring::where(function ($query) use ($sales) {
            if ($sales) {
                $query->where('sales_id', $sales);
            }
        })
        ->where(function ($query) use ($waktu) {
            if ($waktu == 'today') {
                $query->whereDate('followup_date', now()->toDateString());
            } else if ($waktu == 'tomorrow') {
                $query->whereDate('followup_date', now()->addDay()->toDateString());
            }
        })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($data);
    }

    public function getStatistikPenjualan(Request $request)
    {
        $sales = $request->created_id;
        $filter = $request->filter; // harian, mingguan, bulanan
        $query = Transaksi::where(function ($query) use ($sales) {
            if ($sales) {
            $query->where('created_id', $sales);
            }
        });

        if ($filter == 'harian') {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($filter == 'mingguan') {
            $query->whereBetween('created_at', [
            now()->startOfWeek(), now()->endOfWeek()
            ]);
        } else { // bulanan (default)
            $query->whereMonth('created_at', now()->month)
              ->whereYear('created_at', now()->year);
        }

        $data = $query->orderBy('created_at', 'desc')->get();
        $grandTotal = $data->sum('grand_total');

        return response()->json($data);
    }

    public function getRealisasi(Request $request)
    {
        $today = now()->toDateString();
        $startOfWeek = now()->startOfWeek()->toDateString();
        $endOfWeek = now()->endOfWeek()->toDateString();
        $month = now()->month;
        $year = now()->year;

        // Hari ini
        $realisasiHariIni = Target::whereDate('tanggal_awal', $today)->sum('min_penjualan');

        // Minggu ini
        $realisasiMingguIni = Target::whereDate('tanggal_awal', '>=', $startOfWeek)
            ->whereDate('tanggal_awal', '<=', $endOfWeek)
            ->sum('min_penjualan');

        // Bulan ini
        $realisasiBulanIni = Target::whereMonth('tanggal_awal', $month)
            ->whereYear('tanggal_awal', $year)
            ->sum('min_penjualan');

        return response()->json([
            'hari_ini' => $realisasiHariIni,
            'minggu_ini' => $realisasiMingguIni,
            'bulan_ini' => $realisasiBulanIni,
        ]);
    }

    public function getRingkasanPenjualan(Request $request)
    {
        $sales = $request->created_id;
        $filter = $request->filter; // harian, mingguan, bulanan
        $query = Transaksi::with(['konsumen.prospek'])
            ->where(function ($query) use ($sales) {
            if ($sales) {
                $query->where('created_id', $sales);
            }
            });

        if ($filter == 'harian') {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($filter == 'mingguan') {
            $query->whereBetween('created_at', [
            now()->startOfWeek(), now()->endOfWeek()
            ]);
        } else { // bulanan (default)
            $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
        }

        $data = $query->get();

        // Grouping transaksi berdasarkan prospek_id
        $grouped = $data->groupBy(function ($item) {
            return optional(optional($item->konsumen)->prospek)->id;
        });

        // Sum grand_total per prospek
        $result = [];
        foreach ($grouped as $prospekId => $transaksis) {
            $result[] = [
            'prospek_id' => $prospekId,
            'grand_total' => $transaksis->sum('grand_total'),
            'transaksis' => $transaksis,
            ];
        }

    }
}
