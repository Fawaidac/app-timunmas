<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SalesOrder;
use App\Models\SalesVisit;
use App\Models\User;
use Carbon\Carbon;

class LaporanController extends Controller
{
    public function index()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $lastMonthDate = now()->subMonth();
        $lastMonth = $lastMonthDate->month;
        $lastMonthYear = $lastMonthDate->year;

        // =========================================================================
        // 1. TOTAL REVENUE BULAN INI (Model Payment)
        // =========================================================================
        // Menghitung sum 'amount_paid' di mana status = 'approved'
        // dan approved_at/created_at di bulan ini
        $totalRevenueBulanIni = (float) Payment::whereRaw('LOWER(status) = ?', ['approved'])
            ->where(function ($q) use ($currentMonth, $currentYear) {
                $q->whereMonth('approved_at', $currentMonth)
                  ->whereYear('approved_at', $currentYear)
                  ->orWhere(function ($sq) use ($currentMonth, $currentYear) {
                      $sq->whereNull('approved_at')
                         ->whereMonth('created_at', $currentMonth)
                         ->whereYear('created_at', $currentYear);
                  });
            })
            ->sum('amount_paid');

        // Revenue Bulan Lalu
        $revenueLastMonth = (float) Payment::whereRaw('LOWER(status) = ?', ['approved'])
            ->where(function ($q) use ($lastMonth, $lastMonthYear) {
                $q->whereMonth('approved_at', $lastMonth)
                  ->whereYear('approved_at', $lastMonthYear)
                  ->orWhere(function ($sq) use ($lastMonth, $lastMonthYear) {
                      $sq->whereNull('approved_at')
                         ->whereMonth('created_at', $lastMonth)
                         ->whereYear('created_at', $lastMonthYear);
                  });
            })
            ->sum('amount_paid');

        // Indikator Pertumbuhan Revenue
        if ($revenueLastMonth > 0) {
            $diff = (($totalRevenueBulanIni - $revenueLastMonth) / $revenueLastMonth) * 100;
            $revenueGrowthHint = ($diff >= 0 ? '↑ ' : '↓ ') . abs(round($diff, 1)) . '% vs bln lalu';
        } else {
            $revenueGrowthHint = 'Bulan berjalan';
        }

        // =========================================================================
        // 2. TOTAL ORDER DISETUJUI (Model SalesOrder)
        // =========================================================================
        $totalOrderDisetujui = SalesOrder::whereRaw('LOWER(status) = ?', ['approved'])
            ->where(function ($q) use ($currentMonth, $currentYear) {
                $q->whereMonth('order_date', $currentMonth)
                  ->whereYear('order_date', $currentYear)
                  ->orWhere(function ($sq) use ($currentMonth, $currentYear) {
                      $sq->whereMonth('created_at', $currentMonth)
                         ->whereYear('created_at', $currentYear);
                  });
            })
            ->count();

        $ordersLastMonth = SalesOrder::whereRaw('LOWER(status) = ?', ['approved'])
            ->where(function ($q) use ($lastMonth, $lastMonthYear) {
                $q->whereMonth('order_date', $lastMonth)
                  ->whereYear('order_date', $lastMonthYear)
                  ->orWhere(function ($sq) use ($lastMonth, $lastMonthYear) {
                      $sq->whereMonth('created_at', $lastMonth)
                         ->whereYear('created_at', $lastMonthYear);
                  });
            })
            ->count();

        if ($ordersLastMonth > 0) {
            $diffOrder = (($totalOrderDisetujui - $ordersLastMonth) / $ordersLastMonth) * 100;
            $orderHint = ($diffOrder >= 0 ? '↑ ' : '↓ ') . abs(round($diffOrder, 1)) . '% vs bln lalu';
        } else {
            $orderHint = 'Bulan berjalan';
        }

        // =========================================================================
        // 3. AVERAGE ORDER VALUE / AOV (Model SalesOrder)
        // =========================================================================
        $approvedOrdersQuery = SalesOrder::whereRaw('LOWER(status) = ?', ['approved']);
        $approvedOrdersCount = $approvedOrdersQuery->count();

        if ($approvedOrdersCount > 0) {
            $aov = (float) ($approvedOrdersQuery->sum('total_amount') / $approvedOrdersCount);
        } else {
            $aov = 0.0;
        }

        $aovGrowthHint = 'Per transaksi';

        // =========================================================================
        // 4. KUNJUNGAN SELESAI (%) (Model SalesVisit)
        // =========================================================================
        // Menerima status 'selesai' maupun 'completed'
        $totalVisitsThisMonth = SalesVisit::where(function ($q) use ($currentMonth, $currentYear) {
            $q->whereMonth('visit_date', $currentMonth)
              ->whereYear('visit_date', $currentYear)
              ->orWhere(function ($sq) use ($currentMonth, $currentYear) {
                  $sq->whereMonth('created_at', $currentMonth)
                     ->whereYear('created_at', $currentYear);
              });
        })->count();

        $completedVisitsThisMonth = SalesVisit::where(function ($q) {
            $q->whereRaw('LOWER(status) = ?', ['selesai'])
              ->orWhereRaw('LOWER(status) = ?', ['completed']);
        })
        ->where(function ($q) use ($currentMonth, $currentYear) {
            $q->whereMonth('visit_date', $currentMonth)
              ->whereYear('visit_date', $currentYear)
              ->orWhere(function ($sq) use ($currentMonth, $currentYear) {
                  $sq->whereMonth('created_at', $currentMonth)
                     ->whereYear('created_at', $currentYear);
              });
        })
        ->count();

        $kunjunganSelesaiPercent = $totalVisitsThisMonth > 0
            ? round(($completedVisitsThisMonth / $totalVisitsThisMonth) * 100, 1)
            : 0;

        // =========================================================================
        // GRAFIK 1: Tren Penjualan 6 Bulan Terakhir (Payment Revenue)
        // =========================================================================
        $monthlyTrendLabels = [];
        $monthlyTrendData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyTrendLabels[] = $date->translatedFormat('M Y');

            $revenue = Payment::whereRaw('LOWER(status) = ?', ['approved'])
                ->where(function ($q) use ($date) {
                    $q->whereMonth('approved_at', $date->month)
                      ->whereYear('approved_at', $date->year)
                      ->orWhere(function ($sq) use ($date) {
                          $sq->whereNull('approved_at')
                             ->whereMonth('created_at', $date->month)
                             ->whereYear('created_at', $date->year);
                      });
                })
                ->sum('amount_paid');

            $monthlyTrendData[] = (float) $revenue;
        }

        // =========================================================================
        // GRAFIK 2: Performa Penjualan Per Sales (Menggunakan Payment atau Order)
        // =========================================================================
        $topSalesData = User::where('role', 'sales')->get()
            ->map(function ($sales) {
                // 1. Hitung total pembayaran yang sudah di-approve
                $revenue = Payment::where('sales_id', $sales->id)
                    ->whereRaw('LOWER(status) = ?', ['approved'])
                    ->sum('amount_paid');

                // 2. Jika payment 0, fallback hitung total Sales Order approved
                if ($revenue == 0) {
                    $revenue = SalesOrder::where('sales_id', $sales->id)
                        ->whereRaw('LOWER(status) = ?', ['approved'])
                        ->sum('total_amount');
                }

                return [
                    'name'    => $sales->name,
                    'revenue' => (float) $revenue,
                ];
            })
            ->sortByDesc('revenue') // Urutkan dari revenue terbesar
            ->take(5);              // 👈 Cuma ambil 5 Sales Teratas!

        // Extract data untuk dikirim ke view
        $salesLabels = $topSalesData->pluck('name')->values()->toArray();
        $salesData   = $topSalesData->pluck('revenue')->values()->toArray();

        // =========================================================================
        // RETURN VIEW
        // =========================================================================
        return view('admin.laporan.index', [
            'totalRevenueBulanIni'    => $totalRevenueBulanIni,
            'revenueGrowthHint'       => $revenueGrowthHint,
            'totalOrderDisetujui'     => $totalOrderDisetujui,
            'orderHint'               => $orderHint,
            'aov'                     => $aov,
            'aovGrowthHint'           => $aovGrowthHint,
            'kunjunganSelesaiPercent' => $kunjunganSelesaiPercent,
            'monthlyTrendLabels'      => $monthlyTrendLabels,
            'monthlyTrendData'        => $monthlyTrendData,
            'salesLabels'             => $salesLabels,
            'salesData'               => $salesData,
        ]);
    }
}