<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index()
    {
        $kdPeg = auth()->user()->KD_PEG;
        $m = (int) now()->month;
        $y = (int) now()->year;
        $pegFilter = $kdPeg
            ? " AND KD_PEG = '" . addcslashes($kdPeg, "'") . "'"
            : ' AND 1=0';

        $totalVisits = (int) DB::selectOne("
            SELECT COUNT(*) AS JML FROM KUNJUNGAN
            WHERE EXTRACT(MONTH FROM TANGGAL) = {$m}
              AND EXTRACT(YEAR FROM TANGGAL) = {$y}
              {$pegFilter}
        ")->JML;

        $completedVisits = (int) DB::selectOne("
            SELECT COUNT(*) AS JML FROM KUNJUNGAN
            WHERE STATUS = 'completed'
              AND EXTRACT(MONTH FROM TANGGAL) = {$m}
              AND EXTRACT(YEAR FROM TANGGAL) = {$y}
              {$pegFilter}
        ")->JML;

        $productivity = $totalVisits > 0 ? round(($completedVisits / $totalVisits) * 100) : 0;

        $visitWithOrderCount = (int) DB::selectOne("
            SELECT COUNT(*) AS JML FROM KUNJUNGAN
            WHERE STATUS = 'completed' AND NO_ENT_ORD IS NOT NULL
              AND EXTRACT(MONTH FROM TANGGAL) = {$m}
              AND EXTRACT(YEAR FROM TANGGAL) = {$y}
              {$pegFilter}
        ")->JML;

        $strikeRate = $completedVisits > 0 ? round(($visitWithOrderCount / $completedVisits) * 100) : 0;

        $agg = DB::selectOne("
            SELECT COUNT(*) AS JML, COALESCE(SUM(TOTAL), 0) AS TOTAL
            FROM MST_ORD_JUAL
            WHERE ST_JADI <> 'BATAL'
              AND EXTRACT(MONTH FROM TANGGAL) = {$m}
              AND EXTRACT(YEAR FROM TANGGAL) = {$y}
              {$pegFilter}
        ");
        $totalOrderCount = (int) $agg->JML;
        $totalOrderAmount = (float) $agg->TOTAL;
        $averageOrder = $totalOrderCount > 0 ? ($totalOrderAmount / $totalOrderCount) : 0;

        $totalApprovedPayments = (float) DB::selectOne("
            SELECT COALESCE(SUM(JUMLAH), 0) AS TOTAL FROM PAYMENT
            WHERE STATUS = 'approved'
              AND EXTRACT(MONTH FROM COALESCE(TGL_APPROVE, TANGGAL)) = {$m}
              AND EXTRACT(YEAR FROM COALESCE(TGL_APPROVE, TANGGAL)) = {$y}
              {$pegFilter}
        ")->TOTAL;

        $collectionRate = $totalOrderAmount > 0 ? round(($totalApprovedPayments / $totalOrderAmount) * 100) : 0;

        $weeklySalesLabels = ['Minggu 1', 'Minggu 2', 'Minggu 3', 'Minggu 4'];
        $weeklySalesData = [0, 0, 0, 0];

        foreach (DB::select("
            SELECT TANGGAL, TOTAL FROM MST_ORD_JUAL
            WHERE ST_JADI <> 'BATAL'
              AND EXTRACT(MONTH FROM TANGGAL) = {$m}
              AND EXTRACT(YEAR FROM TANGGAL) = {$y}
              {$pegFilter}
        ") as $row) {
            $w = (int) ceil(Carbon::parse($row->TANGGAL)->day / 7);
            if ($w >= 1 && $w <= 4) {
                $weeklySalesData[$w - 1] += (float) $row->TOTAL;
            }
        }

        $purposeCounts = [];
        foreach (DB::select("
            SELECT TUJUAN, COUNT(*) AS TOTAL FROM KUNJUNGAN
            WHERE EXTRACT(MONTH FROM TANGGAL) = {$m}
              AND EXTRACT(YEAR FROM TANGGAL) = {$y}
              {$pegFilter}
            GROUP BY TUJUAN
        ") as $row) {
            $purposeCounts[$row->TUJUAN] = (int) $row->TOTAL;
        }

        $chartPurposeLabels = [];
        $chartPurposeData = [];
        foreach (['order' => 'Order Barang', 'collection' => 'Penagihan', 'merchandising' => 'Merchandising'] as $key => $label) {
            $chartPurposeLabels[] = $label;
            $chartPurposeData[] = $purposeCounts[$key] ?? 0;
        }

        return view('sales.laporan.index', compact(
            'productivity', 'completedVisits', 'totalVisits', 'strikeRate',
            'visitWithOrderCount', 'averageOrder', 'totalOrderCount', 'collectionRate',
            'weeklySalesLabels', 'weeklySalesData', 'chartPurposeLabels', 'chartPurposeData'
        ));
    }
}