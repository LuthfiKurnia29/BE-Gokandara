<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FollowupMonitoring;
use App\Models\Konsumen;
use App\Models\Projek;
use App\Models\Properti;
use App\Models\Prospek;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller {
    public function getFollowUpToday(Request $request) {
        $today = Carbon::today()->format('Y-m-d');
        $user = Auth::user();
        $dateStart = $request->dateStart;
        $dateEnd = $request->dateEnd;
        $prospek_id = $request->prospek_id;
        $status = $request->status;

        $query = FollowupMonitoring::query();

        if (isset($request->created_id)) {
            $query->where('sales_id', $request->created_id);
        } else if ($user->hasRole('Admin')) {
            // Admin sees all
        } elseif ($user->hasRole('Supervisor')) {
            // Get subordinate sales IDs
            $subordinateIds = $user->getSubordinateIds();
            $subordinateIds[] = $user->id;
            $query->whereIn('sales_id', $subordinateIds);
        } elseif ($user->hasRole('Telemarketing')) {
            // Get konsumen IDs assigned by this telemarketing user
            $assignedKonsumenIds = $user->getAssignedKonsumenIds();
            $assignedKonsumenIds[] = $user->id;
            $query->whereIn('konsumen_id', $assignedKonsumenIds);
        } else {
            $query->where('sales_id', Auth::id());
        }

        $query->whereDate('followup_date', $today);

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

        $followUps = $query->with('konsumen')->get();
        // This method can be used to return a dashboard view or data
        return response()->json(
            [
                'message' => 'successfully retrieved follow-ups for today',
                'status' => 'success',
                'data' => $followUps,
                'count' => $followUps->count(),
            ],
            200,
        );
    }

    public function getFollowUpTomorrow(Request $request) {
        $tomorrow = Carbon::now()->addDay()->format('Y-m-d');
        $user = Auth::user();
        $dateStart = $request->dateStart;
        $dateEnd = $request->dateEnd;
        $prospek_id = $request->prospek_id;
        $status = $request->status;

        $query = FollowupMonitoring::query();

        if (isset($request->created_id)) {
            $query->where('sales_id', $request->created_id);
        } else if ($user->hasRole('Admin')) {
            // Admin sees all
        } elseif ($user->hasRole('Supervisor')) {
            // Get subordinate sales IDs
            $subordinateIds = $user->getSubordinateIds();
            $subordinateIds[] = $user->id;
            $query->whereIn('sales_id', $subordinateIds);
        } elseif ($user->hasRole('Telemarketing')) {
            // Get konsumen IDs assigned by this telemarketing user
            $assignedKonsumenIds = $user->getAssignedKonsumenIds();
            $assignedKonsumenIds[] = $user->id;
            $query->whereIn('konsumen_id', $assignedKonsumenIds);
        } else {
            $query->where('sales_id', Auth::id());
        }

        $query->whereDate('followup_date', $tomorrow);

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

        $followUps = $query->with('konsumen')->get();
        return response()->json(
            [
                'message' => 'successfully retrieved follow-ups for this week',
                'status' => 'success',
                'data' => $followUps,
                'count' => $followUps->count(),
            ],
            200,
        );
    }

    public function getNewKonsumens(Request $request) {
        $user = Auth::user();
        $dateStart = $request->dateStart;
        $dateEnd = $request->dateEnd;
        $prospek_id = $request->prospek_id;
        $status = $request->status;

        $query = Konsumen::query();

        if (isset($request->created_id)) {
            $query->where('created_id', $request->created_id);
        } else if ($user->hasRole('Admin')) {
            // Admin sees all
        } elseif ($user->hasRole('Supervisor')) {
            // Get subordinate sales IDs
            $subordinateIds = $user->getSubordinateIds();
            $subordinateIds[] = $user->id;
            $query->whereIn('created_id', $subordinateIds);
        } elseif ($user->hasRole('Telemarketing')) {
            // Get konsumen IDs assigned by this telemarketing user
            $assignedKonsumenIds = $user->getAssignedKonsumenIds();
            $assignedKonsumenIds[] = $user->id;
            $query->whereIn('id', $assignedKonsumenIds);
        } else {
            $query->where('created_id', Auth::id());
        }

        $query->whereDate('created_at', Carbon::today());

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

        $newKonsumens = $query->get();
        return response()->json(
            [
                'message' => 'successfully retrieved new konsumens',
                'status' => 'success',
                'data' => $newKonsumens,
                'count' => $newKonsumens->count(),
            ],
            200,
        );
    }

    public function getKonsumenByProspek(Request $request) {
        $user = Auth::user();
        $dateStart = $request->dateStart;
        $dateEnd = $request->dateEnd;
        $prospek_id = $request->prospek_id;
        $status = $request->status;

        $query = Konsumen::select('prospek_id', DB::raw('count(*) as total'));

        // Get count of konsumen grouped by prospek_id
        if (isset($request->created_id)) {
            $query->where('created_id', $request->created_id);
        } else if ($user->hasRole('Admin')) {
            // Admin sees all
        } elseif ($user->hasRole('Supervisor')) {
            // Get subordinate sales IDs
            $subordinateIds = $user->getSubordinateIds();
            $subordinateIds[] = $user->id;
            $query->whereIn('created_id', $subordinateIds);
        } elseif ($user->hasRole('Telemarketing')) {
            // Get konsumen IDs assigned by this telemarketing user
            $assignedKonsumenIds = $user->getAssignedKonsumenIds();
            $assignedKonsumenIds[] = $user->id;
            $query->whereIn('id', $assignedKonsumenIds);
        } else {
            $query->where('created_id', Auth::id());
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

        $konsumenByProspek = $query->groupBy('prospek_id')->get();

        // Get all prospek data to include names
        $prospeks = Prospek::all();

        // Format data for pie chart
        $chartData = [];
        $labels = [];
        $values = [];

        foreach ($prospeks as $prospek) {
            $count = 0;
            foreach ($konsumenByProspek as $item) {
                if ($item->prospek_id == $prospek->id) {
                    $count = $item->total;
                    break;
                }
            }

            $labels[] = $prospek->name ?? 'Prospek ' . $prospek->id;
            $values[] = $count;
            $percentage = $konsumenByProspek->sum('total') > 0 ? round(($count / $konsumenByProspek->sum('total')) * 100) : 0;

            $chartData[] = [
                'name' => $prospek->name ?? 'Prospek ' . $prospek->id,
                'value' => $count,
                'percentage' => $percentage . '%',
                'color' => $prospek->color,
            ];
        }

        return response()->json(
            [
                'message' => 'successfully retrieved konsumen by prospek data',
                'status' => 'success',
                'data' => [
                    'chart_data' => $chartData,
                    'labels' => $labels,
                    'values' => $values,
                ],
            ],
            200,
        );
    }

    public function getSalesOverview(Request $request) {
        // Get year from request, default to current year
        $year = $request->input('year', Carbon::now()->year);
        $user = Auth::user();
        $dateStart = $request->dateStart;
        $dateEnd = $request->dateEnd;
        $prospek_id = $request->prospek_id;
        $status = $request->status ?? 'Akad'; // Default to 'Akad' if no status provided

        // Define months for the chart
        $months = ['April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember', 'Januari', 'Februari', 'Maret'];

        // Initialize data arrays
        $totalTerjual = [];
        $totalDipesan = [];

        // Get data for each month
        foreach ($months as $index => $month) {
            // Calculate the actual month number (April is month 4)
            $monthNumber = ($index + 4) % 12;
            if ($monthNumber == 0) {
                $monthNumber = 12;
            }

            // Determine the year for this month (some months might be in the next year)
            $queryYear = $year;
            if ($monthNumber < 4) {
                $queryYear = $year + 1;
            }

            if($status){
                $transaksi = Transaksi::where('status', $status);
            }else {
                $transaksi = Transaksi::query();
            }

            // Build base query for terjual
            $terjualQuery = $transaksi->whereYear('created_at', $queryYear)
                ->whereMonth('created_at', $monthNumber);

            // Build base query for dipesan
            $dipesanQuery = $transaksi->whereYear('created_at', $queryYear)
                ->whereMonth('created_at', $monthNumber);

            // Apply role-based filtering
            if (isset($request->created_id)) {
                $terjualQuery->where('created_id', $request->created_id);
                $dipesanQuery->where('created_id', $request->created_id);
            } else if ($user->hasRole('Admin')) {
                // Admin: tampilkan semua data
            } elseif ($user->hasRole('Supervisor')) {
                // Supervisor: tampilkan data dari subordinate sales
                $subordinateIds = $user->getSubordinateIds();
                $subordinateIds[] = $user->id;
                $terjualQuery->whereIn('created_id', $subordinateIds);
                $dipesanQuery->whereIn('created_id', $subordinateIds);
            } elseif ($user->hasRole('Telemarketing')) {
                // Telemarketing: tampilkan data dari konsumen yang di-assign
                $assignedKonsumenIds = $user->getAssignedKonsumenIds();
                $assignedKonsumenIds[] = $user->id;
                $terjualQuery->whereIn('konsumen_id', $assignedKonsumenIds);
                $dipesanQuery->whereIn('konsumen_id', $assignedKonsumenIds);
            } else {
                // Selain admin: tampilkan data sesuai yang login
                $terjualQuery->where('created_id', Auth::id());
                $dipesanQuery->where('created_id', Auth::id());
            }

            // Apply additional filters
            if ($dateStart && $dateEnd) {
                $terjualQuery->whereBetween('created_at', [$dateStart, $dateEnd]);
                $dipesanQuery->whereBetween('created_at', [$dateStart, $dateEnd]);
            }

            if ($prospek_id) {
                $terjualQuery->whereHas('konsumen', function ($q) use ($prospek_id) {
                    $q->where('prospek_id', $prospek_id);
                });
                $dipesanQuery->whereHas('konsumen', function ($q) use ($prospek_id) {
                    $q->where('prospek_id', $prospek_id);
                });
            }

            $terjual = $terjualQuery->sum('grand_total');
            $dipesan = $dipesanQuery->count();

            $totalTerjual[] = $terjual;
            $totalDipesan[] = $dipesan;
        }

        // Calculate total for the summary boxes
        $totalTerjualSum = array_sum($totalTerjual);
        $totalDipesanSum = array_sum($totalDipesan);

        // Calculate percentage change compared to previous week
        // For this example, we'll just return 0% as placeholder
        $percentageChange = '0,0%';

        return response()->json(
            [
                'message' => 'successfully retrieved sales overview data',
                'status' => 'success',
                'data' => [
                    'summary' => [
                        'total_terjual' => [
                            'value' => $totalTerjualSum,
                            'unit' => 'Unit',
                            'percentage_change' => $percentageChange,
                        ],
                        'total_dipesan' => [
                            'value' => $totalDipesanSum,
                            'unit' => 'Unit',
                            'percentage_change' => $percentageChange,
                        ],
                    ],
                    'chart' => [
                        'months' => $months,
                        'series' => [
                            [
                                'name' => 'Total Terjual',
                                'data' => $totalTerjual,
                            ],
                            [
                                'name' => 'Total Dipesan',
                                'data' => $totalDipesan,
                            ],
                        ],
                    ],
                ],
            ],
            200,
        );
    }

    public function getTransaksiByProperti(Request $request) {
        $user = Auth::user();
        $dateStart = $request->dateStart;
        $dateEnd = $request->dateEnd;
        $prospek_id = $request->prospek_id;
        $status = $request->status ?? 'Akad'; // Default to 'Akad' if no status provided

        $query = Transaksi::select('projeks_id', DB::raw('count(*) as total'));

        // Get count of transaksi grouped by projeks_id
        if (isset($request->created_id)) {
            $query->where('created_id', $request->created_id);
        } else if ($user->hasRole('Admin')) {
            // Admin: tampilkan semua data
        } elseif ($user->hasRole('Supervisor')) {
            // Supervisor: tampilkan data dari subordinate sales
            $subordinateIds = $user->getSubordinateIds();
            $subordinateIds[] = $user->id;
            $query->whereIn('created_id', $subordinateIds);
        } elseif ($user->hasRole('Telemarketing')) {
            // Telemarketing: tampilkan data dari konsumen yang di-assign
            $assignedKonsumenIds = $user->getAssignedKonsumenIds();
            $assignedKonsumenIds[] = $user->id;
            $query->whereIn('konsumen_id', $assignedKonsumenIds);
        } else {
            // Selain admin: tampilkan data sesuai yang login
            $query->where('created_id', Auth::id());
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

        $transaksiByProperti = $query->groupBy('projeks_id')->get();

        // Get all properti data to include names
        $propertis = Projek::withSum('tipes', 'jumlah_unit')->get();

        // Format data for chart
        $chartData = [];
        $labels = [];
        $values = [];

        // Define colors for the chart
        $colors = [
            '#FF6384', // Red
            '#36A2EB', // Blue
            '#FFCE56', // Yellow
            '#4BC0C0', // Teal
            '#9966FF', // Purple
            '#FF9F40', // Orange
            '#8AC249', // Green
            '#EA5F89', // Pink
            '#00D8B6', // Turquoise
            '#FF8A80', // Light Red
        ];

        $colorIndex = 0;
        foreach ($propertis as $properti) {
            $count = 0;
            foreach ($transaksiByProperti as $item) {
                if ($item->projeks_id == $properti->id) {
                    $count = $item->total;
                    break;
                }
            }

            $labels[] = $properti->name ?? 'Properti ' . $properti->id;
            $values[] = $count;
            $percentage = $transaksiByProperti->sum('total') > 0 ? round(($count / $transaksiByProperti->sum('total')) * 100) : 0;

            // Assign color from the array, cycling through if needed
            $color = $colors[$colorIndex % count($colors)];
            $colorIndex++;

            $chartData[] = [
                'name' => $properti->name ?? 'Properti ' . $properti->id,
                'value' => $count,
                'total_unit' => $properti->tipes_sum_jumlah_unit,
                'percentage' => $percentage . '%',
                'color' => $color,
            ];
        }

        return response()->json(
            [
                'message' => 'successfully retrieved transaksi by properti data',
                'status' => 'success',
                'data' => [
                    'chart_data' => $chartData,
                    'labels' => $labels,
                    'values' => $values,
                ],
            ],
            200,
        );
    }
}
