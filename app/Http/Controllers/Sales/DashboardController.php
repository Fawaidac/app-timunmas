<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\SalesVisit;
use App\Models\SalesOrder;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $salesId = auth()->id();
        $today = Carbon::today();
        $userName = auth()->user()->name;

        // Kunjungan hari ini
        $kunjunganHariIni = SalesVisit::where('sales_id', $salesId)
            ->whereDate('visit_date', $today)
            ->get();
        
        $totalKunjunganHariIni = $kunjunganHariIni->count();
        $kunjunganSelesai = $kunjunganHariIni->where('status', 'completed')->count();

        // Order hari ini
        $orderHariIni = SalesOrder::where('sales_id', $salesId)
            ->whereDate('order_date', $today)
            ->get();
        
        $totalOrderHariIni = $orderHariIni->sum('total_amount');
        
        // Order kemarin untuk perbandingan
        $yesterday = Carbon::yesterday();
        $orderKemarin = SalesOrder::where('sales_id', $salesId)
            ->whereDate('order_date', $yesterday)
            ->sum('total_amount');
        
        $percentageChange = 0;
        if ($orderKemarin > 0) {
            $percentageChange = (($totalOrderHariIni - $orderKemarin) / $orderKemarin) * 100;
        }

        // Tagihan jatuh tempo (hari ini dan yang lewat)
        $tagihanJatuhTempo = Invoice::whereHas('order', function($q) use ($salesId) {
                $q->where('sales_id', $salesId);
            })
            ->where('status', '!=', 'paid')
            ->where('due_date', '<=', $today)
            ->get();
        
        $jumlahTagihanJatuhTempo = $tagihanJatuhTempo->count();
        $nilaiTagihanJatuhTempo = $tagihanJatuhTempo->sum('remaining_balance');

        // Pembayaran dititipkan (pending approval)
        $pembayaranDititipkan = Payment::where('sales_id', $salesId)
            ->where('status', 'pending_approval')
            ->get();
        
        $jumlahPembayaranDititipkan = $pembayaranDititipkan->count();
        $nilaiPembayaranDititipkan = $pembayaranDititipkan->sum('amount_paid');

        // Order terbaru (5 terakhir)
        $orderTerbaru = SalesOrder::with('customer')
            ->where('sales_id', $salesId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($order) {
                // Map status ke badge
                $badgeMap = [
                    'pending' => ['class' => 'badge-warning', 'label' => 'Menunggu'],
                    'processing' => ['class' => 'badge-orange', 'label' => 'Diproses'],
                    'approved' => ['class' => 'badge-success', 'label' => 'Disetujui'],
                    'completed' => ['class' => 'badge-success', 'label' => 'Selesai'],
                    'cancelled' => ['class' => 'badge-danger', 'label' => 'Dibatalkan'],
                ];
                
                $order->badge_class = $badgeMap[$order->status]['class'] ?? 'badge-secondary';
                $order->badge_label = $badgeMap[$order->status]['label'] ?? ucfirst($order->status);
                
                return $order;
            });

        // Rute kunjungan hari ini (ordered by visit_date)
        $ruteKunjungan = SalesVisit::with('customer')
            ->where('sales_id', $salesId)
            ->whereDate('visit_date', $today)
            ->orderBy('visit_date', 'asc')
            ->get()
            ->map(function($visit) {
                $time = Carbon::parse($visit->visit_date)->format('H:i');
                $hour = Carbon::parse($visit->visit_date)->format('H');
                
                // Status label
                $statusMap = [
                    'scheduled' => 'Terjadwal',
                    'in_progress' => 'Sedang dikunjungi',
                    'completed' => 'Selesai',
                    'cancelled' => 'Dibatalkan',
                ];
                
                $visit->hour = $hour;
                $visit->time_label = $time;
                $visit->status_label = $statusMap[$visit->status] ?? ucfirst($visit->status);
                
                return $visit;
            });

        // Greeting berdasarkan waktu
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
