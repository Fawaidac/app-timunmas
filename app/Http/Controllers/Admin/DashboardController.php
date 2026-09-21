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

/**
 * Dashboard admin — agregat dari tabel legacy:
 * MST_ORD_JUAL (order), VW_PIUTANG (piutang), PAYMENT (workflow web),
 * MUTASI_BARANG (stok), PEGAWAI (sales aktif).
 */
class DashboardController extends Controller
{
    public function index()
    {
        // Orders bulan ini & lalu (MST_ORD_JUAL)
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

        // Revenue = payment approved (PAYMENT web)
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

        // Sales aktif (PEGAWAI STS_SALES=YA) & yang punya order hari ini
        $totalSales = (int) Pegawai::salesAktif()->count();
        $activeSalesToday = (int) DB::table('MST_ORD_JUAL')
            ->whereRaw('CAST(TANGGAL AS DATE) = ?', [today()->toDateString()])
            ->distinct()->value('KD_PEG') ? 1 : 0;
        $activeSalesToday = (int) DB::table('MST_ORD_JUAL')
            ->whereRaw('CAST(TANGGAL AS DATE) = ?', [today()->toDateString()])
            ->whereNotNull('KD_PEG')
            ->distinct()
            ->count('KD_PEG');

        // Pending approval (PAYMENT web)
        $pendingOrders = (int) Payment::where('STATUS', 'pending_approval')->count();

        // Recent orders (6 terbaru)
        $recentOrders = SalesOrder::with('customer')
            ->orderBy('TANGGAL', 'desc')
            ->take(6)
            ->get();

        // Top sales (omzet dari REKAP_OMZET_EFF_CALL legacy)
        $topSales = collect(DB::select('
            SELECT FIRST 5 KD_PEG, TOTAL_OMZET, BYK_FAKTUR
            FROM REKAP_OMZET_EFF_CALL
            ORDER BY TOTAL_OMZET DESC
        '))->map(fn ($r) => (object) [
            'code' => trim($r->KD_PEG),
            'name' => (string) (Pegawai::find(trim($r->KD_PEG))->NM_PEG ?? trim($r->KD_PEG)),
            'total_revenue' => (float) $r->TOTAL_OMZET,
        ]);

        // Pending payments = piutang belum lunas (VW_PIUTANG)
        $pendingPayments = Invoice::with('customer')
            ->where('SISA_PIUTANG', '>', 0.005)
            ->orderBy('TGL_JATUH_TEMPO', 'asc')
            ->take(6)
            ->get()
            ->each(function ($inv) {
                $inv->badge_status = $inv->status === 'overdue' ? 'danger' : 'info';
            });

        // Low stock (MUTASI_BARANG QTY_AKHIR < 20)
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
