<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Helpers\SalesHelper;
use App\Models\SalesVisit;
use App\Models\SalesOrder;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard sales — sumber: KUNJUNGAN (web), MST_ORD_JUAL, VW_PIUTANG, PAYMENT.
 */
class DashboardController extends Controller
{
    public function index()
    {
        $kdPeg    = SalesHelper::kdPeg();
        $today    = Carbon::today();
        $userName = SalesHelper::nama();

        $scopedPeg = fn ($q) => $kdPeg ? $q->where('KD_PEG', $kdPeg) : $q->whereRaw('1=0');

        // Kunjungan hari ini
        $totalKunjunganHariIni = (int) SalesVisit::query()
            ->where($scopedPeg)
            ->whereRaw('CAST(TANGGAL AS DATE) = ?', [$today->toDateString()])
            ->count();

        $kunjunganSelesai = (int) SalesVisit::query()
            ->where($scopedPeg)
            ->where('STATUS', 'completed')
            ->whereRaw('CAST(TANGGAL AS DATE) = ?', [$today->toDateString()])
            ->count();

        // Order hari ini & kemarin (MST_ORD_JUAL)
        $totalOrderHariIni = (float) DB::table('MST_ORD_JUAL')
            ->where($scopedPeg)
            ->whereRaw('CAST(TANGGAL AS DATE) = ?', [$today->toDateString()])
            ->sum('TOTAL');

        $orderKemarin = (float) DB::table('MST_ORD_JUAL')
            ->where($scopedPeg)
            ->whereRaw('CAST(TANGGAL AS DATE) = ?', [Carbon::yesterday()->toDateString()])
            ->sum('TOTAL');

        $percentageChange = $orderKemarin > 0
            ? (($totalOrderHariIni - $orderKemarin) / $orderKemarin) * 100
            : 0;

        // Tagihan jatuh tempo (VW_PIUTANG)
        $jumlahTagihanJatuhTempo = (int) Invoice::query()
            ->where($scopedPeg)
            ->where('SISA_PIUTANG', '>', 0.005)
            ->whereRaw('CAST(TGL_JATUH_TEMPO AS DATE) <= ?', [$today->toDateString()])
            ->count();

        $nilaiTagihanJatuhTempo = (float) Invoice::query()
            ->where($scopedPeg)
            ->where('SISA_PIUTANG', '>', 0.005)
            ->whereRaw('CAST(TGL_JATUH_TEMPO AS DATE) <= ?', [$today->toDateString()])
            ->sum('SISA_PIUTANG');

        // Pembayaran dititipkan (approved)
        $jumlahPembayaranDititipkan = (int) Payment::query()
            ->where($scopedPeg)
            ->where('STATUS', 'approved')
            ->count();

        $nilaiPembayaranDititipkan = (float) Payment::query()
            ->where($scopedPeg)
            ->where('STATUS', 'approved')
            ->sum('JUMLAH');

        // Order terbaru
        $orderTerbaru = SalesOrder::with('customer')
            ->where($scopedPeg)
            ->orderBy('TANGGAL', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($order) {
                $badgeMap = [
                    'OS'    => ['class' => 'badge-warning', 'label' => 'Menunggu faktur'],
                    'INV'   => ['class' => 'badge-success', 'label' => 'Sudah faktur'],
                    'BATAL' => ['class' => 'badge-danger', 'label' => 'Dibatalkan'],
                ];

                $order->badge_class = $badgeMap[$order->ST_JADI]['class'] ?? 'badge-secondary';
                $order->badge_label = $badgeMap[$order->ST_JADI]['label'] ?? ucfirst((string) $order->ST_JADI);

                return $order;
            });

        // Rute kunjungan hari ini
        $ruteKunjungan = SalesVisit::with('customer')
            ->where($scopedPeg)
            ->whereRaw('CAST(TANGGAL AS DATE) = ?', [$today->toDateString()])
            ->orderBy('TANGGAL', 'asc')
            ->get()
            ->map(function ($visit) {
                $visit->hour = $visit->TANGGAL ? Carbon::parse($visit->TANGGAL)->format('H') : '00';
                $visit->time_label = $visit->TANGGAL ? Carbon::parse($visit->TANGGAL)->format('H:i') : '-';

                return $visit;
            });

        $currentHour = Carbon::now()->hour;
        if ($currentHour < 12) {
            $greeting = 'Selamat pagi';
        } elseif ($currentHour < 18) {
            $greeting = 'Selamat siang';
        } else {
            $greeting = 'Selamat malam';
        }

        return view('sales.dashboard.index', compact(
            'userName',
            'greeting',
            'totalKunjunganHariIni',
            'kunjunganSelesai',
            'totalOrderHariIni',
            'percentageChange',
            'jumlahTagihanJatuhTempo',
            'nilaiTagihanJatuhTempo',
            'jumlahPembayaranDititipkan',
            'nilaiPembayaranDititipkan',
            'orderTerbaru',
            'ruteKunjungan'
        ));
    }
}
