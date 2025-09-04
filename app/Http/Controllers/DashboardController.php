<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\FollowupMonitoring;
use App\Models\Konsumen;
use App\Models\Properti;
use App\Models\Prospek;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function getFollowUpToday()
    {
        $today = Carbon::today()->format('Y-m-d');
        if (auth()->user()->hasRole('Admin')) {
            $followUps = FollowupMonitoring::whereDate('followup_date', $today)
            ->with('konsumen')
            ->get();
        } else {
            $followUps = FollowupMonitoring::where('sales_id', auth()->id())
            ->whereDate('followup_date', $today)
            ->with('konsumen')
            ->get();
        }
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

    public function getFollowUpTomorrow()
    {
        $tomorrow = Carbon::now()->addDay()->format('Y-m-d');
        if (auth()->user()->hasRole('Admin')) {
            $followUps = FollowupMonitoring::whereDate('followup_date', $tomorrow)
            ->with('konsumen')
            ->get();
        } else {
            $followUps = FollowupMonitoring::where('sales_id', auth()->id())
            ->whereDate('followup_date', $tomorrow)
            ->with('konsumen')
            ->get();
        }
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

    public function getNewKonsumens()
    {
        if (auth()->user()->hasRole('Admin')) {
            $newKonsumens = Konsumen::whereDate('created_at', Carbon::today())->get();
        } else {
            $newKonsumens = Konsumen::where('created_id', auth()->id())
            ->whereDate('created_at', Carbon::today())
            ->get();
        }
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

    public function getKonsumenByProspek()
    {
        // Get count of konsumen grouped by prospek_id
        if (auth()->user()->hasRole('Admin')) {
            $konsumenByProspek = Konsumen::select('prospek_id', DB::raw('count(*) as total'))
            ->groupBy('prospek_id')
            ->get();
        } else {
            $konsumenByProspek = Konsumen::select('prospek_id', DB::raw('count(*) as total'))
            ->where('created_id', auth()->id())
            ->groupBy('prospek_id')
            ->get();
        }

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

    public function getSalesOverview(Request $request)
    {
        // Get year from request, default to current year
        $year = $request->input('year', Carbon::now()->year);

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

            // Get total terjual (sum of transaksi grand_total) for this month
            if (auth()->user()->hasRole('Admin')) {
                // Admin: tampilkan semua data
                $terjual = Transaksi::whereYear('created_at', $queryYear)
                    ->whereMonth('created_at', $monthNumber)
                    ->sum('grand_total');

                $dipesan = Konsumen::whereYear('created_at', $queryYear)
                    ->whereMonth('created_at', $monthNumber)
                    ->count();
            } else {
                // Selain admin: tampilkan data sesuai yang login
                $terjual = Transaksi::whereYear('created_at', $queryYear)
                    ->whereMonth('created_at', $monthNumber)
                    ->where('created_id', auth()->id())
                    ->sum('grand_total');

                $dipesan = Konsumen::whereYear('created_at', $queryYear)
                    ->whereMonth('created_at', $monthNumber)
                    ->where('created_id', auth()->id())
                    ->count();
            }

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

    public function getTransaksiByProperti()
    {
        // Get count of transaksi grouped by properti_id
        if (auth()->user()->hasRole('Admin')) {
            // Admin: tampilkan semua data
            $transaksiByProperti = Transaksi::select('properti_id', DB::raw('count(*) as total'))
            ->groupBy('properti_id')
            ->get();
        } else {
            // Selain admin: tampilkan data sesuai yang login
            $transaksiByProperti = Transaksi::select('properti_id', DB::raw('count(*) as total'))
            ->where('created_id', auth()->id())
            ->groupBy('properti_id')
            ->get();
        }

        // Get all properti data to include names
        $propertis = Properti::all();

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
                if ($item->properti_id == $properti->id) {
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
