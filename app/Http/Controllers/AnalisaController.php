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

class AnalisaController extends Controller
{
    public function getNewKonsumen(Request $request)
    {
        $sales = $request->created_id;
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            $data = Konsumen::orderBy('created_at', 'desc')->get()->take(4);
        } elseif ($user->hasRole('Supervisor')) {
            // Get subordinate sales IDs
            $subordinateIds = $user->getSubordinateIds();
            $data = Konsumen::whereIn('created_id', $subordinateIds)
                ->where(function ($query) use ($sales) {
                    if ($sales) {
                        $query->where('created_id', $sales);
                    }
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->take(4);
        } elseif ($user->hasRole('Telemarketing')) {
            // Get konsumen IDs assigned by this telemarketing user
            $assignedKonsumenIds = $user->getAssignedKonsumenIds();
            $data = Konsumen::whereIn('id', $assignedKonsumenIds)
                ->where(function ($query) use ($sales) {
                    if ($sales) {
                        $query->where('created_id', $sales);
                    }
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->take(4);
        } else {
            $data = Konsumen::where('created_id', Auth::id())
                ->where(function ($query) use ($sales) {
                    if ($sales) {
                        $query->where('created_id', $sales);
                    }
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->take(4);
        }

        return response()->json($data);
    }

    public function getFollowup(Request $request)
    {
        $sales = $request->created_id;
        $waktu = $request->waktu; // today, tomorrow
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
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
        } elseif ($user->hasRole('Supervisor')) {
            // Get subordinate sales IDs
            $subordinateIds = $user->getSubordinateIds();
            $data = FollowupMonitoring::whereIn('sales_id', $subordinateIds)
                ->where(function ($query) use ($sales) {
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
        } elseif ($user->hasRole('Telemarketing')) {
            // Get konsumen IDs assigned by this telemarketing user
            $assignedKonsumenIds = $user->getAssignedKonsumenIds();
            $data = FollowupMonitoring::whereIn('konsumen_id', $assignedKonsumenIds)
                ->where(function ($query) use ($sales) {
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
        } else {
            $data = FollowupMonitoring::where('sales_id', Auth::id())
                ->where(function ($query) use ($sales) {
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
        }

        return response()->json(['count_data' => $data->count()]);
    }

    public function getStatistikPenjualan(Request $request)
    {
        $sales = $request->created_id;
        $filter = $request->filter; // harian, mingguan, bulanan
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            $query = Transaksi::where(function ($query) use ($sales) {
                if ($sales) {
                    $query->where('created_id', $sales);
                }
            });
        } elseif ($user->hasRole('Supervisor')) {
            // Get subordinate sales IDs
            $subordinateIds = $user->getSubordinateIds();
            $query = Transaksi::whereIn('created_id', $subordinateIds)
                ->where(function ($query) use ($sales) {
                    if ($sales) {
                        $query->where('created_id', $sales);
                    }
                });
        } elseif ($user->hasRole('Telemarketing')) {
            // Get konsumen IDs assigned by this telemarketing user
            $assignedKonsumenIds = $user->getAssignedKonsumenIds();
            $query = Transaksi::whereIn('konsumen_id', $assignedKonsumenIds)
                ->where(function ($query) use ($sales) {
                    if ($sales) {
                        $query->where('created_id', $sales);
                    }
                });
        } else {
            $query = Transaksi::where('created_id', Auth::id())->where(function ($query) use ($sales) {
                if ($sales) {
                    $query->where('created_id', $sales);
                }
            });
        }

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
        $user = Auth::user();

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
        $transaksiHariIni = Transaksi::whereDate('created_at', $today);
        $transaksiMingguIni = Transaksi::whereDate('created_at', '>=', $startOfWeek)->whereDate('created_at', '<=', $endOfWeek);
        $transaksiBulanIni = Transaksi::whereMonth('created_at', $month)->whereYear('created_at', $year);

        if ($user->hasRole('Supervisor')) {
            // Get subordinate sales IDs
            $subordinateIds = $user->getSubordinateIds();
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
            if ($sales) {
                $transaksiHariIni->where('created_id', $sales->id)->whereIn('konsumen_id', $assignedKonsumenIds);
                $transaksiMingguIni->where('created_id', $sales->id)->whereIn('konsumen_id', $assignedKonsumenIds);
                $transaksiBulanIni->where('created_id', $sales->id)->whereIn('konsumen_id', $assignedKonsumenIds);
            } else {
                $transaksiHariIni->whereIn('konsumen_id', $assignedKonsumenIds);
                $transaksiMingguIni->whereIn('konsumen_id', $assignedKonsumenIds);
                $transaksiBulanIni->whereIn('konsumen_id', $assignedKonsumenIds);
            }
        } else {
            if ($sales) {
                $transaksiHariIni->where('created_id', $sales->id);
                $transaksiMingguIni->where('created_id', $sales->id);
                $transaksiBulanIni->where('created_id', $sales->id);
            } else {
                $transaksiHariIni->where('created_id', Auth::id());
                $transaksiMingguIni->where('created_id', Auth::id());
                $transaksiBulanIni->where('created_id', Auth::id());
            }
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
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            $query = Transaksi::with(['konsumen.prospek'])->where(function ($query) use ($sales) {
                if ($sales) {
                    $query->where('created_id', $sales);
                }
            });
        } elseif ($user->hasRole('Supervisor')) {
            // Get subordinate sales IDs
            $subordinateIds = $user->getSubordinateIds();
            $query = Transaksi::with(['konsumen.prospek'])
                ->whereIn('created_id', $subordinateIds)
                ->where(function ($query) use ($sales) {
                    if ($sales) {
                        $query->where('created_id', $sales);
                    }
                });
        } elseif ($user->hasRole('Telemarketing')) {
            // Get konsumen IDs assigned by this telemarketing user
            $assignedKonsumenIds = $user->getAssignedKonsumenIds();
            $query = Transaksi::with(['konsumen.prospek'])
                ->whereIn('konsumen_id', $assignedKonsumenIds)
                ->where(function ($query) use ($sales) {
                    if ($sales) {
                        $query->where('created_id', $sales);
                    }
                });
        } else {
            $query = Transaksi::with(['konsumen.prospek'])
                ->where('created_id', Auth::id())
                ->where(function ($query) use ($sales) {
                    if ($sales) {
                        $query->where('created_id', $sales);
                    }
                });
        }

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
        $user = Auth::user();

        if ($user->hasRole('Admin')) {
            $query = Transaksi::where(function ($query) use ($sales) {
                if ($sales) {
                    $query->where('created_id', $sales);
                }
            });
        } elseif ($user->hasRole('Supervisor')) {
            // Get subordinate sales IDs
            $subordinateIds = $user->getSubordinateIds();
            $query = Transaksi::whereIn('created_id', $subordinateIds)
                ->where(function ($query) use ($sales) {
                    if ($sales) {
                        $query->where('created_id', $sales);
                    }
                });
        } elseif ($user->hasRole('Telemarketing')) {
            // Get konsumen IDs assigned by this telemarketing user
            $assignedKonsumenIds = $user->getAssignedKonsumenIds();
            $query = Transaksi::whereIn('konsumen_id', $assignedKonsumenIds)
                ->where(function ($query) use ($sales) {
                    if ($sales) {
                        $query->where('created_id', $sales);
                    }
                });
        } else {
            $query = Transaksi::where('created_id', Auth::id())->where(function ($query) use ($sales) {
                if ($sales) {
                    $query->where('created_id', $sales);
                }
            });
        }

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
