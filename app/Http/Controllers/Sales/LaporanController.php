<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SalesOrder;
use App\Models\SalesVisit;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index()
    {
        $salesId = auth()->id();
        $currentMonth = now()->month;
        $currentYear = now()->year;

        // ---------------------------------------------------------------------
        // 1. STATISTIK UTAMA (METRICS)
        // ---------------------------------------------------------------------

        // Total Kunjungan Sales Bulan Ini
        $totalVisits = SalesVisit::where('sales_id', $salesId)
            ->where(function ($q) use ($currentMonth, $currentYear) {
                $q->whereMonth('visit_date', $currentMonth)
                  ->whereYear('visit_date', $currentYear)
                  ->orWhere(function ($sq) use ($currentMonth, $currentYear) {
                      $sq->whereMonth('created_at', $currentMonth)
                         ->whereYear('created_at', $currentYear);
                  });
            })->count();

        // Kunjungan Selesai (Completed / Selesai)
        $completedVisits = SalesVisit::where('sales_id', $salesId)
            ->where(function ($q) {
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
            })->count();

        // 1. Produktivitas Kunjungan (% Kunjungan Selesai dari Total Jadwal)
        $productivity = $totalVisits > 0 ? round(($completedVisits / $totalVisits) * 100) : 0;

        // 2. Strike Rate (% Kunjungan Selesai yang Memiliki Sales Order)
        $visitWithOrderCount = SalesVisit::where('sales_id', $salesId)
            ->whereHas('order')
            ->where(function ($q) use ($currentMonth, $currentYear) {
                $q->whereMonth('visit_date', $currentMonth)
                  ->whereYear('visit_date', $currentYear)
                  ->orWhere(function ($sq) use ($currentMonth, $currentYear) {
                      $sq->whereMonth('created_at', $currentMonth)
                         ->whereYear('created_at', $currentYear);
                  });
            })->count();

        $strikeRate = $completedVisits > 0 ? round(($visitWithOrderCount / $completedVisits) * 100) : 0;

        // 3. Average Order Value (AOV Sales Ini)
        $salesOrdersQuery = SalesOrder::where('sales_id', $salesId)
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->where(function ($q) use ($currentMonth, $currentYear) {
                $q->whereMonth('order_date', $currentMonth)
                  ->whereYear('order_date', $currentYear)
                  ->orWhere(function ($sq) use ($currentMonth, $currentYear) {
                      $sq->whereMonth('created_at', $currentMonth)
                         ->whereYear('created_at', $currentYear);
                  });
            });

        $totalOrderCount = $salesOrdersQuery->count();
        $totalOrderAmount = $salesOrdersQuery->sum('total_amount');
        $averageOrder = $totalOrderCount > 0 ? ($totalOrderAmount / $totalOrderCount) : 0;

        // 4. Collection Rate (% Pembayaran Approved vs Total Nilai Order)
        $totalApprovedPayments = Payment::where('sales_id', $salesId)
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->where(function ($q) use ($currentMonth, $currentYear) {
                $q->whereMonth('approved_at', $currentMonth)
                  ->whereYear('approved_at', $currentYear)
                  ->orWhere(function ($sq) use ($currentMonth, $currentYear) {
                      $sq->whereNull('approved_at')
                         ->whereMonth('created_at', $currentMonth)
                         ->whereYear('created_at', $currentYear);
                  });
            })->sum('amount_paid');

        $collectionRate = $totalOrderAmount > 0 ? round(($totalApprovedPayments / $totalOrderAmount) * 100) : 0;

        // ---------------------------------------------------------------------
        // 2. GRAFIK 1: TREN PENJUALAN MINGGUAN (4 MINGGU BULAN INI)
        // ---------------------------------------------------------------------
        $weeklySalesLabels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'];
        $weeklySalesData = [0, 0, 0, 0];

        $startOfMonth = now()->startOfMonth();
        $ordersThisMonth = SalesOrder::where('sales_id', $salesId)
            ->whereRaw('LOWER(status) = ?', ['approved'])
            ->whereMonth('order_date', $currentMonth)
            ->whereYear('order_date', $currentYear)
            ->get();

        foreach ($ordersThisMonth as $order) {
            $orderDate = Carbon::parse($order->order_date ?? $order->created_at);
            $weekNumber = ceil($orderDate->day / 7);
            if ($weekNumber >= 1 && $weekNumber <= 4) {
                $weeklySalesData[$weekNumber - 1] += (float) $order->total_amount;
            }
        }

        // ---------------------------------------------------------------------
        // 3. GRAFIK 2: KOMPOSISI TUJUAN KUNJUNGAN (PURPOSE)
        // ---------------------------------------------------------------------
        $purposeCounts = SalesVisit::where('sales_id', $salesId)
            ->where(function ($q) use ($currentMonth, $currentYear) {
                $q->whereMonth('visit_date', $currentMonth)
                  ->whereYear('visit_date', $currentYear)
                  ->orWhere(function ($sq) use ($currentMonth, $currentYear) {
                      $sq->whereMonth('created_at', $currentMonth)
                         ->whereYear('created_at', $currentYear);
                  });
            })
            ->selectRaw('purpose, count(*) as total')
            ->groupBy('purpose')
            ->pluck('total', 'purpose')
            ->toArray();

        // Pemetaan Label
        $purposeLabels = [
            'order'         => 'Order Barang',
            'collection'    => 'Penagihan',
            'merchandising' => 'Merchandising',
        ];

        $chartPurposeLabels = [];
        $chartPurposeData = [];

        foreach ($purposeLabels as $key => $label) {
            $chartPurposeLabels[] = $label;
            $chartPurposeData[] = $purposeCounts[$key] ?? 0;
        }

        return view('sales.laporan.index', compact(
            'productivity',
            'completedVisits',
            'totalVisits',
            'strikeRate',
            'visitWithOrderCount',
            'averageOrder',
            'totalOrderCount',
            'collectionRate',
            'weeklySalesLabels',
            'weeklySalesData',
            'chartPurposeLabels',
            'chartPurposeData'
        ));
    }
}