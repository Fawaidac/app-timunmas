<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\WarehouseStock;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOrders = (int) DB::table('MST_ORD_JUAL')->where('ST_JADI', '!=', 'BATAL')->count();

        $ordersThisMonth = (int) DB::table('MST_ORD_JUAL')
            ->whereRaw('EXTRACT(MONTH FROM TANGGAL) = ? AND EXTRACT(YEAR FROM TANGGAL) = ?', [now()->month, now()->year])
            ->count();

        $ordersLastMonth = (int) DB::table('MST_ORD_JUAL')
            ->whereRaw('EXTRACT(MONTH FROM TANGGAL) = ? AND EXTRACT(YEAR FROM TANGGAL) = ?', [now()->subMonth()->month, now()->subMonth()->year])
            ->count();

        $ordersGrowth = $ordersLastMonth > 0
            ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100)
            : 0;

        $totalRevenue = (float) Payment::where('STATUS', 'approved')->sum('JUMLAH');

        $revenueThisMonth = (float) Payment::where('STATUS', 'approved')
            ->whereRaw('EXTRACT(MONTH FROM TGL_APPROVE) = ? AND EXTRACT(YEAR FROM TGL_APPROVE) = ?', [now()->month, now()->year])
            ->sum('JUMLAH');

        $revenueLastMonth = (float) Payment::where('STATUS', 'approved')
            ->whereRaw('EXTRACT(MONTH FROM TGL_APPROVE) = ? AND EXTRACT(YEAR FROM TGL_APPROVE) = ?', [now()->subMonth()->month, now()->subMonth()->year])
            ->sum('JUMLAH');

        $revenueGrowth = $revenueLastMonth > 0
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100)
            : 0;

        $totalSales = (int) Pegawai::salesAktif()->count();
        $activeSalesToday = (int) DB::table('MST_ORD_JUAL')
            ->whereRaw('CAST(TANGGAL AS DATE) = ?', [today()->toDateString()])
            ->distinct()->value('KD_PEG') ? 1 : 0;
        $activeSalesToday = (int) DB::table('MST_ORD_JUAL')
            ->whereRaw('CAST(TANGGAL AS DATE) = ?', [today()->toDateString()])
            ->whereNotNull('KD_PEG')
            ->distinct()
            ->count('KD_PEG');

        $pendingOrders = (int) Payment::where('STATUS', 'pending_approval')->count();

        $recentOrders = SalesOrder::with('customer')
            ->orderBy('TANGGAL', 'desc')
            ->take(6)
            ->get();

        $topSales = collect(DB::select('
            SELECT FIRST 5 KD_PEG, TOTAL_OMZET, BYK_FAKTUR
            FROM REKAP_OMZET_EFF_CALL
            ORDER BY TOTAL_OMZET DESC
        '))->map(fn ($r) => (object) [
            'code' => trim($r->KD_PEG),
            'name' => (string) (Pegawai::find(trim($r->KD_PEG))->NM_PEG ?? trim($r->KD_PEG)),
            'total_revenue' => (float) $r->TOTAL_OMZET,
        ]);

        $pendingPayments = Invoice::with('customer')
            ->where('SISA_PIUTANG', '>', 0.005)
            ->orderBy('TGL_JATUH_TEMPO', 'asc')
            ->take(6)
            ->get()
            ->each(function ($inv) {
                $inv->badge_status = $inv->status === 'overdue' ? 'danger' : 'info';
            });

        $lowStockProducts = WarehouseStock::with(['product', 'warehouse'])
            ->where('QTY_AKHIR', '<', 20)
            ->orderBy('QTY_AKHIR', 'asc')
            ->take(6)
            ->get()
            ->map(fn ($stock) => (object) [
                'name' => $stock->product->name ?? $stock->KD_BRG,
                'stock' => (float) $stock->QTY_AKHIR,
                'warehouse' => (object) ['name' => $stock->warehouse->name ?? $stock->GUDANG],
            ]);

        return view('admin.dashboard.index', compact(
            'totalOrders',
            'ordersGrowth',
            'totalRevenue',
            'revenueGrowth',
            'totalSales',
            'activeSalesToday',
            'pendingOrders',
            'recentOrders',
            'topSales',
            'pendingPayments',
            'lowStockProducts'
        ));
    }
}
