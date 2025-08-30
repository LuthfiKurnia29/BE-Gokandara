<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Konsumen;
use App\Models\FollowupMonitoring;
use App\Models\Transaksi;
use App\Models\Target;
use App\Models\Prospek;

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
            ->get()->take(4);

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
                } elseif ($waktu == 'tomorrow') {
                    $query->whereDate('followup_date', now()->addDay()->toDateString());
                }
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['count_data' => $data->count()]);
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
            $query->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
        } elseif ($filter == 'mingguan') {
            $query->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()]);
        } else {
            $query->whereBetween('created_at', [now()->subYear()->startOfDay(), now()->endOfDay()]);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        // Grouping berdasarkan filter
        if ($filter == 'harian') {
            $grouped = $data->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            });
        } elseif ($filter == 'mingguan') {
            $grouped = $data->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            });
        } else {
            // bulanan
            $grouped = $data->groupBy(function ($item) {
                return $item->created_at->format('Y-m');
            });
        }

        // Format hasil
        $result = [];
        foreach ($grouped as $key => $items) {
            $result[] = [
                'periode' => $key,
                'grand_total' => $items->sum('grand_total'),
                'transaksis' => $items,
            ];
        }

        return response()->json($result);
    }

    public function getRealisasi(Request $request)
    {
        $sales = $request->created_id;
        $today = now()->toDateString();
        $startOfWeek = now()->startOfWeek()->toDateString();
        $endOfWeek = now()->endOfWeek()->toDateString();
        $month = now()->month;
        $year = now()->year;

        // Target
        $targetHariIni = Target::whereDate('tanggal_awal', $today);
        $targetMingguIni = Target::whereDate('tanggal_awal', '>=', $startOfWeek)->whereDate('tanggal_awal', '<=', $endOfWeek);
        $targetBulanIni = Target::whereMonth('tanggal_awal', $month)->whereYear('tanggal_awal', $year);

        if ($sales) {
            $targetHariIni->where('sales_id', $sales);
            $targetMingguIni->where('sales_id', $sales);
            $targetBulanIni->where('sales_id', $sales);
        }

        $targetHariIni = $targetHariIni->sum('min_penjualan');
        $targetMingguIni = $targetMingguIni->sum('min_penjualan');
        $targetBulanIni = $targetBulanIni->sum('min_penjualan');

        // Penjualan (Transaksi)
        $transaksiHariIni = Transaksi::whereDate('created_at', $today);
        $transaksiMingguIni = Transaksi::whereDate('created_at', '>=', $startOfWeek)->whereDate('created_at', '<=', $endOfWeek);
        $transaksiBulanIni = Transaksi::whereMonth('created_at', $month)->whereYear('created_at', $year);

        if ($sales) {
            $transaksiHariIni->where('created_id', $sales);
            $transaksiMingguIni->where('created_id', $sales);
            $transaksiBulanIni->where('created_id', $sales);
        }

        $penjualanHariIni = $transaksiHariIni->sum('grand_total');
        $penjualanMingguIni = $transaksiMingguIni->sum('grand_total');
        $penjualanBulanIni = $transaksiBulanIni->sum('grand_total');

        // Persentase realisasi
        $realisasiHariIni = $targetHariIni > 0 ? round(($penjualanHariIni / $targetHariIni) * 100, 2) : 0;
        $realisasiMingguIni = $targetMingguIni > 0 ? round(($penjualanMingguIni / $targetMingguIni) * 100, 2) : 0;
        $realisasiBulanIni = $targetBulanIni > 0 ? round(($penjualanBulanIni / $targetBulanIni) * 100, 2) : 0;

        return response()->json([
            'hari_ini' => $realisasiHariIni,
            'minggu_ini' => $realisasiMingguIni,
            'bulan_ini' => $realisasiBulanIni,
            'target' => [
                'hari_ini' => $targetHariIni,
                'minggu_ini' => $targetMingguIni,
                'bulan_ini' => $targetBulanIni,
            ],
            'penjualan' => [
                'hari_ini' => $penjualanHariIni,
                'minggu_ini' => $penjualanMingguIni,
                'bulan_ini' => $penjualanBulanIni,
            ],
        ]);
    }

    public function getRingkasanPenjualan(Request $request)
    {
        $sales = $request->created_id;
        $filter = $request->filter; // harian, mingguan, bulanan
        $query = Transaksi::with(['konsumen.prospek'])->where(function ($query) use ($sales) {
            if ($sales) {
                $query->where('created_id', $sales);
            }
        });

        if ($filter == 'harian') {
            $query->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
        } elseif ($filter == 'mingguan') {
            $query->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()]);
        } else {
            $query->whereBetween('created_at', [now()->subYear()->startOfDay(), now()->endOfDay()]);
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
                'prospek' => Prospek::find($prospekId),
                'grand_total' => $transaksis->sum('grand_total'),
            ];
        }

        return response()->json($result);
    }

    public function getStatistikPemesanan(Request $request)
    {
        $sales = $request->created_id;
        $filter = $request->filter; // harian, mingguan, bulanan
        $query = Transaksi::where(function ($query) use ($sales) {
            if ($sales) {
                $query->where('created_id', $sales);
            }
        });

        if ($filter == 'harian') {
            $query->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
        } elseif ($filter == 'mingguan') {
            $query->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()]);
        } else {
            $query->whereBetween('created_at', [now()->subYear()->startOfDay(), now()->endOfDay()]);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        // Grouping berdasarkan filter
        if ($filter == 'harian' || $filter == 'mingguan') {
            $grouped = $data->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            });
        } else {
            // bulanan
            $grouped = $data->groupBy(function ($item) {
                return $item->created_at->format('Y-m');
            });
        }

        // Format hasil
        $result = [];
        foreach ($grouped as $key => $items) {
            $result[] = [
                'periode' => $key,
                'total_pemesanan' => $items->count(),
                'transaksis' => $items,
            ];
        }

        return response()->json($result);
    }
}
