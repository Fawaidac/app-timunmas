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

class DashboardController extends Controller
{
    public function index()
    {
        $kdPeg    = SalesHelper::kdPeg();
        $today    = Carbon::today();
        $userName = SalesHelper::nama();

        $scopedPeg = fn ($q) => $kdPeg ? $q->where('KD_PEG', $kdPeg) : $q->whereRaw('1=0');

        $totalKunjunganHariIni = (int) SalesVisit::query()
            ->where($scopedPeg)
            ->whereRaw('CAST(TANGGAL AS DATE) = ?', [$today->toDateString()])
            ->count();

        $kunjunganSelesai = (int) SalesVisit::query()
            ->where($scopedPeg)
            ->where('STATUS', 'completed')
            ->whereRaw('CAST(TANGGAL AS DATE) = ?', [$today->toDateString()])
            ->count();

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

        $jumlahPembayaranDititipkan = (int) Payment::query()
            ->where($scopedPeg)
            ->where('STATUS', 'approved')
            ->count();

        $nilaiPembayaranDititipkan = (float) Payment::query()
            ->where($scopedPeg)
            ->where('STATUS', 'approved')
            ->sum('JUMLAH');

        $orderTerbaru = SalesOrder::with(['customer', 'payments'])
            ->where($scopedPeg)
            ->orderBy('TANGGAL', 'desc')
            ->limit(5)
            ->get(); // badge_class/badge_label dihitung model (Lunas / Kredit / Draft / dst)

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
