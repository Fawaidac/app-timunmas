<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\SalesVisit;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index()
    {
        $m = (int) now()->month;
        $y = (int) now()->year;

        $totalRevenueBulanIni = (float) DB::selectOne("
            SELECT COALESCE(SUM(JUMLAH), 0) AS TOTAL FROM PAYMENT
            WHERE STATUS = 'approved'
              AND EXTRACT(MONTH FROM COALESCE(TGL_APPROVE, TANGGAL)) = {$m}
              AND EXTRACT(YEAR FROM COALESCE(TGL_APPROVE, TANGGAL)) = {$y}
        ")->TOTAL;

        $lm = now()->subMonth();
        $revenueLastMonth = (float) DB::selectOne("
            SELECT COALESCE(SUM(JUMLAH), 0) AS TOTAL FROM PAYMENT
            WHERE STATUS = 'approved'
              AND EXTRACT(MONTH FROM COALESCE(TGL_APPROVE, TANGGAL)) = " . (int) $lm->month . "
              AND EXTRACT(YEAR FROM COALESCE(TGL_APPROVE, TANGGAL)) = " . (int) $lm->year . "
        ")->TOTAL;

        if ($revenueLastMonth > 0) {
            $diff = (($totalRevenueBulanIni - $revenueLastMonth) / $revenueLastMonth) * 100;
            $revenueGrowthHint = ($diff >= 0 ? '↑ ' : '↓ ') . abs(round($diff, 1)) . '% vs bln lalu';
        } else {
            $revenueGrowthHint = 'Bulan berjalan';
        }

        $orderAgg = DB::selectOne("
            SELECT COUNT(*) AS JML, COALESCE(SUM(TOTAL), 0) AS TOTAL
            FROM MST_ORD_JUAL
            WHERE ST_JADI <> 'BATAL'
              AND EXTRACT(MONTH FROM TANGGAL) = {$m}
              AND EXTRACT(YEAR FROM TANGGAL) = {$y}
        ");
        $totalOrderDisetujui = (int) $orderAgg->JML;
        $orderAmount = (float) $orderAgg->TOTAL;

        $orderAggLast = (int) DB::selectOne("
            SELECT COUNT(*) AS JML FROM MST_ORD_JUAL
            WHERE ST_JADI <> 'BATAL'
              AND EXTRACT(MONTH FROM TANGGAL) = " . (int) $lm->month . "
              AND EXTRACT(YEAR FROM TANGGAL) = " . (int) $lm->year . "
        ")->JML;

        $orderHint = $orderAggLast > 0
            ? (($totalOrderDisetujui >= $orderAggLast ? '↑ ' : '↓ ') . abs($totalOrderDisetujui - $orderAggLast) . ' order vs bln lalu')
            : 'Bulan berjalan';

        $aov = $totalOrderDisetujui > 0 ? $orderAmount / $totalOrderDisetujui : 0;
        $aovGrowthHint = 'AOV bulan ini';

        $totalVisitsThisMonth = (int) DB::selectOne("
            SELECT COUNT(*) AS JML FROM KUNJUNGAN
            WHERE EXTRACT(MONTH FROM TANGGAL) = {$m} AND EXTRACT(YEAR FROM TANGGAL) = {$y}
        ")->JML;

        $completedVisitsThisMonth = (int) DB::selectOne("
            SELECT COUNT(*) AS JML FROM KUNJUNGAN
            WHERE STATUS = 'completed'
              AND EXTRACT(MONTH FROM TANGGAL) = {$m} AND EXTRACT(YEAR FROM TANGGAL) = {$y}
        ")->JML;

        $kunjunganSelesaiPercent = $totalVisitsThisMonth > 0
            ? round(($completedVisitsThisMonth / $totalVisitsThisMonth) * 100, 1)
            : 0;

        $monthlyTrendLabels = [];
        $monthlyTrendData = [];

        for ($i = 5; $i >= 0; $i--) {
            $d = Carbon::now()->subMonths($i);
            $monthlyTrendLabels[] = $d->translatedFormat('M Y');

            $rev = (float) DB::selectOne("
                SELECT COALESCE(SUM(TOTAL), 0) AS TOTAL FROM MST_ORD_JUAL
                WHERE ST_JADI <> 'BATAL'
                  AND EXTRACT(MONTH FROM TANGGAL) = " . (int) $d->month . "
                  AND EXTRACT(YEAR FROM TANGGAL) = " . (int) $d->year . "
            ")->TOTAL;

            $monthlyTrendData[] = $rev;
        }

        $topSalesData = collect(DB::select('
            SELECT FIRST 5 KD_PEG, TOTAL_OMZET FROM REKAP_OMZET_EFF_CALL
            ORDER BY TOTAL_OMZET DESC
        '))->map(fn ($r) => [
            'name'    => (string) (Pegawai::find(trim($r->KD_PEG))->NM_PEG ?? trim($r->KD_PEG)),
            'revenue' => (float) $r->TOTAL_OMZET,
        ]);

        $salesLabels = $topSalesData->pluck('name')->values()->toArray();
        $salesData   = $topSalesData->pluck('revenue')->values()->toArray();

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