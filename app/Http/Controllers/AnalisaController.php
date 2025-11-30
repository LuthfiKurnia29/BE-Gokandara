<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Konsumen;
use App\Models\FollowupMonitoring;
use App\Models\Transaksi;
use App\Models\Target;
use App\Models\Prospek;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AnalisaController extends Controller {
    public function getNewKonsumen(Request $request) {
        $sales = $request->created_id;
        $user = Auth::user();
        $dateStart = $request->dateStart;
        $dateEnd = $request->dateEnd;
        $prospek_id = $request->prospek_id;
        $status = $request->status;

        $query = Konsumen::query();

        if ($user->hasRole('Admin')) {
            if ($sales) {
                $query->where('created_id', $sales);
            }
        } elseif ($user->hasRole('Supervisor')) {
            // Get subordinate sales IDs
            $subordinateIds = $user->getSubordinateIds();
            $subordinateIds[] = $user->id;
            $query->whereIn('created_id', $subordinateIds);
            if ($sales) {
                $query->where('created_id', $sales);
            }
        } elseif ($user->hasRole('Telemarketing')) {
            // Get konsumen IDs assigned by this telemarketing user
            $assignedKonsumenIds = $user->getAssignedKonsumenIds();
            $assignedKonsumenIds[] = $user->id;
            $query->whereIn('id', $assignedKonsumenIds);
        } else {
            $query->where('created_id', Auth::id());
            if ($sales) {
                $query->where('created_id', $sales);
            }
        }

        // Apply additional filters
        if ($dateStart && $dateEnd) {
            $query->whereBetween('created_at', [$dateStart, $dateEnd]);
        }

        if ($prospek_id) {
            $query->where('prospek_id', $prospek_id);
        }

        if ($status) {
            $query->whereHas('latestTransaksi', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        $data = $query->orderBy('created_at', 'desc')->get()->take(5);

        return response()->json(
            [
                'message' => 'successfully retrieved new konsumens',
                'status' => 'success',
                'data' => $data,
                'count' => $data->count(),
            ],
            200,
        );
        //return response()->json($data);
    }

    public function getFollowup(Request $request) {
        $sales = $request->created_id;
        $waktu = $request->waktu; // today, tomorrow
        $user = Auth::user();
        $dateStart = $request->dateStart;
        $dateEnd = $request->dateEnd;
        $prospek_id = $request->prospek_id;
        $status = $request->status;

        $query = FollowupMonitoring::query();

        if ($user->hasRole('Admin')) {
            if ($sales) {
                $query->where('sales_id', $sales);
            }
        } elseif ($user->hasRole('Supervisor')) {
            // Get subordinate sales IDs
            $subordinateIds = $user->getSubordinateIds();
            $subordinateIds[] = $user->id;
            $query->whereIn('sales_id', $subordinateIds);
            if ($sales) {
                $query->where('sales_id', $sales);
            }
        } elseif ($user->hasRole('Telemarketing')) {
            // Get konsumen IDs assigned by this telemarketing user
            $assignedKonsumenIds = $user->getAssignedKonsumenIds();
            $assignedKonsumenIds[] = $user->id;
            $query->whereIn('konsumen_id', $assignedKonsumenIds);
        } else {
            $query->where('sales_id', Auth::id());
            if ($sales) {
                $query->where('sales_id', $sales);
            }
        }

        if ($waktu == 'today') {
            $query->whereDate('followup_date', now()->toDateString());
        } elseif ($waktu == 'tomorrow') {
            $query->whereDate('followup_date', now()->addDay()->toDateString());
        }

        // Apply additional filters
        if ($dateStart && $dateEnd) {
            $query->whereBetween('created_at', [$dateStart, $dateEnd]);
        }

        if ($prospek_id) {
            $query->whereHas('konsumen', function ($q) use ($prospek_id) {
                $q->where('prospek_id', $prospek_id);
            });
        }

        if ($status) {
            $query->whereHas('konsumen.latestTransaksi', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['count_data' => $data->count()]);
    }

    public function getStatistikPenjualan(Request $request) {
        $sales = $request->created_id;
        $filter = $request->filter; // harian, mingguan, bulanan
        $user = Auth::user();
        $dateStart = $request->dateStart;
        $dateEnd = $request->dateEnd;
        $prospek_id = $request->prospek_id;
        $status = $request->status ?? 'Akad';

        if ($status) {
            $query = Transaksi::where('status', $status);
        } else {
            $query = Transaksi::query();
        }

        if ($user->hasRole('Admin')) {
            if ($sales) {
                $query->where('created_id', $sales);
            }
        } elseif ($user->hasRole('Supervisor')) {
            // Get subordinate sales IDs
            $subordinateIds = $user->getSubordinateIds();
            $subordinateIds[] = $user->id;
            $query->whereIn('created_id', $subordinateIds);
            if ($sales) {
                $query->where('created_id', $sales);
            }
        } elseif ($user->hasRole('Telemarketing')) {
            // Get konsumen IDs assigned by this telemarketing user
            $assignedKonsumenIds = $user->getAssignedKonsumenIds();
            $assignedKonsumenIds[] = $user->id;
            $query->whereIn('konsumen_id', $assignedKonsumenIds);
        } else {
            $query->where('created_id', Auth::id());
            if ($sales) {
                $query->where('created_id', $sales);
            }
        }

        if ($filter == 'harian') {
            $query->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
        } elseif ($filter == 'mingguan') {
            $query->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()]);
        } else {
            $query->whereBetween('created_at', [now()->subYear()->startOfDay(), now()->endOfDay()]);
        }

        // Apply additional filters
        if ($dateStart && $dateEnd) {
            $query->whereBetween('created_at', [$dateStart, $dateEnd]);
        }

        if ($prospek_id) {
            $query->whereHas('konsumen', function ($q) use ($prospek_id) {
                $q->where('prospek_id', $prospek_id);
            });
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        // Grouping berdasarkan filter
        if ($filter == 'harian') {
            $grouped = $data->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            });

            // Generate all dates in the last 7 days
            $periods = [];
            for ($i = 6; $i >= 0; $i--) {
                $periods[] = now()->subDays($i)->format('Y-m-d');
            }
        } elseif ($filter == 'mingguan') {
            $grouped = $data->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            });

            // Generate all dates in the last 30 days
            $periods = [];
            for ($i = 29; $i >= 0; $i--) {
                $periods[] = now()->subDays($i)->format('Y-m-d');
            }
        } else {
            // bulanan
            $grouped = $data->groupBy(function ($item) {
                return $item->created_at->format('Y-m');
            });

            // Generate all months in the last 12 months
            $periods = [];
            for ($i = 11; $i >= 0; $i--) {
                $periods[] = now()->subMonths($i)->format('Y-m');
            }
        }

        // Format hasil with placeholders
        $result = [];
        foreach ($periods as $period) {
            if (isset($grouped[$period])) {
                $result[] = [
                    'periode' => $period,
                    'grand_total' => $grouped[$period]->sum('grand_total'),
                    'transaksis' => $grouped[$period],
                ];
            } else {
                // Placeholder for missing data
                $result[] = [
                    'periode' => $period,
                    'grand_total' => 0,
                    'transaksis' => [],
                ];
            }
        }

        return response()->json($result);
    }

    public function getRealisasi(Request $request) {
        $sales = $request->created_id;
        $today = now()->toDateString();
        $startOfWeek = now()->startOfWeek()->toDateString();
        $endOfWeek = now()->endOfWeek()->toDateString();
        $month = now()->month;
        $year = now()->year;
        $user = Auth::user();
        $dateStart = $request->dateStart;
        $dateEnd = $request->dateEnd;
        $prospek_id = $request->prospek_id;
        $status = $request->status;

        // Target
        $targetHariIni = Target::whereDate('tanggal_awal', $today);
        $targetMingguIni = Target::whereDate('tanggal_awal', '>=', $startOfWeek)->whereDate('tanggal_awal', '<=', $endOfWeek);
        $targetBulanIni = Target::whereMonth('tanggal_awal', $month)->whereYear('tanggal_awal', $year);

        if ($sales) {
            $sales = User::find($sales);
            $targetHariIni->where('role_id', $sales->roles[0]->role_id);
            $targetMingguIni->where('role_id', $sales->roles[0]->role_id);
            $targetBulanIni->where('role_id', $sales->roles[0]->role_id);
        }

        $targetHariIni = $targetHariIni->sum('min_penjualan');
        $targetMingguIni = $targetMingguIni->sum('min_penjualan');
        $targetBulanIni = $targetBulanIni->sum('min_penjualan');

        // Penjualan (Transaksi)
        if($status)
        {
            $transaksi = Transaksi::where('status', $status);
        }else {
            //Get all transaksi
            $transaksi = Transaksi::query();
        }

        $transaksiHariIni = $transaksi->whereDate('created_at', $today);
        $transaksiMingguIni = $transaksi->whereDate('created_at', '>=', $startOfWeek)->whereDate('created_at', '<=', $endOfWeek);
        $transaksiBulanIni = $transaksi->whereMonth('created_at', $month)->whereYear('created_at', $year);

        if ($user->hasRole('Supervisor')) {
            // Get subordinate sales IDs
            $subordinateIds = $user->getSubordinateIds();
            $subordinateIds[] = $user->id;
            if ($sales) {
                $transaksiHariIni->where('created_id', $sales->id);
                $transaksiMingguIni->where('created_id', $sales->id);
                $transaksiBulanIni->where('created_id', $sales->id);
            } else {
                $transaksiHariIni->whereIn('created_id', $subordinateIds);
                $transaksiMingguIni->whereIn('created_id', $subordinateIds);
                $transaksiBulanIni->whereIn('created_id', $subordinateIds);
            }
        } elseif ($user->hasRole('Telemarketing')) {
            // Get konsumen IDs assigned by this telemarketing user
            $assignedKonsumenIds = $user->getAssignedKonsumenIds();
            $assignedKonsumenIds[] = $user->id;
            $transaksiHariIni->whereIn('konsumen_id', $assignedKonsumenIds);
            $transaksiMingguIni->whereIn('konsumen_id', $assignedKonsumenIds);
            $transaksiBulanIni->whereIn('konsumen_id', $assignedKonsumenIds);
        } else {
            if ($sales) {
                $transaksiHariIni->where('created_id', $sales->id);
                $transaksiMingguIni->where('created_id', $sales->id);
                $transaksiBulanIni->where('created_id', $sales->id);
            } else if (!$user->hasRole('Admin')) {
                $transaksiHariIni->where('created_id', Auth::id());
                $transaksiMingguIni->where('created_id', Auth::id());
                $transaksiBulanIni->where('created_id', Auth::id());
            }
        }

        // Apply additional filters to all transaksi queries
        if ($dateStart && $dateEnd) {
            $transaksiHariIni->whereBetween('created_at', [$dateStart, $dateEnd]);
            $transaksiMingguIni->whereBetween('created_at', [$dateStart, $dateEnd]);
            $transaksiBulanIni->whereBetween('created_at', [$dateStart, $dateEnd]);
        }

        if ($prospek_id) {
            $transaksiHariIni->whereHas('konsumen', function ($q) use ($prospek_id) {
                $q->where('prospek_id', $prospek_id);
            });
            $transaksiMingguIni->whereHas('konsumen', function ($q) use ($prospek_id) {
                $q->where('prospek_id', $prospek_id);
            });
            $transaksiBulanIni->whereHas('konsumen', function ($q) use ($prospek_id) {
                $q->where('prospek_id', $prospek_id);
            });
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

    public function getRingkasanPenjualan(Request $request) {
        $sales = $request->created_id;
        $filter = $request->filter; // harian, mingguan, bulanan
        $user = Auth::user();
        $dateStart = $request->dateStart;
        $dateEnd = $request->dateEnd;
        $prospek_id = $request->prospek_id;
        $status = $request->status ?? 'Akad';

        $query = Transaksi::with(['konsumen.prospek', 'projek', 'tipe']);

        if ($user->hasRole('Admin')) {
            if ($sales) {
                $query->where('created_id', $sales);
            }
        } elseif ($user->hasRole('Supervisor')) {
            // Get subordinate sales IDs
            $subordinateIds = $user->getSubordinateIds();
            $subordinateIds[] = $user->id;
            $query->whereIn('created_id', $subordinateIds);
            if ($sales) {
                $query->where('created_id', $sales);
            }
        } elseif ($user->hasRole('Telemarketing')) {
            // Get konsumen IDs assigned by this telemarketing user
            $assignedKonsumenIds = $user->getAssignedKonsumenIds();
            $assignedKonsumenIds[] = $user->id;
            $query->whereIn('konsumen_id', $assignedKonsumenIds);
        } else {
            $query->where('created_id', Auth::id());
            if ($sales) {
                $query->where('created_id', $sales);
            }
        }

        if ($filter == 'harian') {
            $query->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
        } elseif ($filter == 'mingguan') {
            $query->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()]);
        } else {
            $query->whereBetween('created_at', [now()->subYear()->startOfDay(), now()->endOfDay()]);
        }

        // Apply additional filters
        if ($dateStart && $dateEnd) {
            $query->whereBetween('created_at', [$dateStart, $dateEnd]);
        }

        if ($prospek_id) {
            $query->whereHas('konsumen', function ($q) use ($prospek_id) {
                $q->where('prospek_id', $prospek_id);
            });
        }

        if ($status) {
            $query->where('status', $status);
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

        $detailPenjualan = $data->map(function ($transaksi) {
            return [
                'projek' => optional($transaksi->projek)->name,
                'tipe' => optional($transaksi->tipe)->name,
                'harga' => $transaksi->grand_total,
            ];
        });

        return response()->json([
            'ringkasan' => $result,
            'detail_penjualan' => $detailPenjualan,
        ]);
    }

    public function getStatistikPemesanan(Request $request) {
        $sales = $request->created_id;
        $filter = $request->filter; // harian, mingguan, bulanan
        $user = Auth::user();
        $dateStart = $request->dateStart;
        $dateEnd = $request->dateEnd;
        $prospek_id = $request->prospek_id;
        $status = $request->status ?? 'Akad';

        $query = Transaksi::query();

        if ($user->hasRole('Admin')) {
            if ($sales) {
                $query->where('created_id', $sales);
            }
        } elseif ($user->hasRole('Supervisor')) {
            // Get subordinate sales IDs
            $subordinateIds = $user->getSubordinateIds();
            $subordinateIds[] = $user->id;
            $query->whereIn('created_id', $subordinateIds);
            if ($sales) {
                $query->where('created_id', $sales);
            }
        } elseif ($user->hasRole('Telemarketing')) {
            // Get konsumen IDs assigned by this telemarketing user
            $assignedKonsumenIds = $user->getAssignedKonsumenIds();
            $assignedKonsumenIds[] = $user->id;
            $query->whereIn('konsumen_id', $assignedKonsumenIds);
        } else {
            $query->where('created_id', Auth::id());
            if ($sales) {
                $query->where('created_id', $sales);
            }
        }

        if ($filter == 'harian') {
            $query->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()]);
        } elseif ($filter == 'mingguan') {
            $query->whereBetween('created_at', [now()->subDays(29)->startOfDay(), now()->endOfDay()]);
        } else {
            $query->whereBetween('created_at', [now()->subYear()->startOfDay(), now()->endOfDay()]);
        }

        // Apply additional filters
        if ($dateStart && $dateEnd) {
            $query->whereBetween('created_at', [$dateStart, $dateEnd]);
        }

        if ($prospek_id) {
            $query->whereHas('konsumen', function ($q) use ($prospek_id) {
                $q->where('prospek_id', $prospek_id);
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $data = $query->orderBy('created_at', 'desc')->get();

        // Grouping berdasarkan filter
        if ($filter == 'harian') {
            $grouped = $data->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            });

            // Generate all dates in the last 7 days
            $periods = [];
            for ($i = 6; $i >= 0; $i--) {
                $periods[] = now()->subDays($i)->format('Y-m-d');
            }
        } elseif ($filter == 'mingguan') {
            $grouped = $data->groupBy(function ($item) {
                return $item->created_at->format('Y-m-d');
            });

            // Generate all dates in the last 30 days
            $periods = [];
            for ($i = 29; $i >= 0; $i--) {
                $periods[] = now()->subDays($i)->format('Y-m-d');
            }
        } else {
            // bulanan
            $grouped = $data->groupBy(function ($item) {
                return $item->created_at->format('Y-m');
            });

            // Generate all months in the last 12 months
            $periods = [];
            for ($i = 11; $i >= 0; $i--) {
                $periods[] = now()->subMonths($i)->format('Y-m');
            }
        }

        // Format hasil with placeholders
        $result = [];
        foreach ($periods as $period) {
            if (isset($grouped[$period])) {
                $result[] = [
                    'periode' => $period,
                    'total_pemesanan' => $grouped[$period]->count(),
                    'transaksis' => $grouped[$period],
                ];
            } else {
                // Placeholder for missing data
                $result[] = [
                    'periode' => $period,
                    'total_pemesanan' => 0,
                    'transaksis' => [],
                ];
            }
        }

        return response()->json($result);
    }
}
