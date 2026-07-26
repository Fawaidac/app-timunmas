<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TagihanController extends Controller
{
    public function index()
    {
        // Get invoices dari customer/order yang disetujui milik sales ini
        $salesId = auth()->id();
        
        $invoices = Invoice::with(['customer', 'order.sales', 'latestPayment'])
            ->whereHas('order', function($q) use ($salesId) {
                $q->where('sales_id', $salesId)
                    ->where('status', '!=', 'cancelled');
            })
            ->where('status', '!=', 'paid')
            ->orderBy('due_date', 'asc')
            ->get();

        // Hitung stats
        $totalPiutang = $invoices->sum('remaining_balance');
        
        $today = Carbon::today();
        $jatuhTempoHariIni = $invoices->filter(function($inv) use ($today) {
            return Carbon::parse($inv->due_date)->isSameDay($today);
        })->sum('remaining_balance');
        
        $lewatJatuhTempo = $invoices->filter(function($inv) use ($today) {
            return Carbon::parse($inv->due_date)->lt($today);
        })->sum('remaining_balance');

        // Tertagih bulan ini = total payment approved bulan ini dari sales ini
        $tertagihBulanIni = Payment::whereHas('invoice.order', function($q) use ($salesId) {
                $q->where('sales_id', $salesId);
            })
            ->where('status', 'approved')
            ->whereMonth('approved_at', Carbon::now()->month)
            ->whereYear('approved_at', Carbon::now()->year)
            ->sum('amount_paid');

        // Format invoices dengan umur dan status badge
        $invoices = $invoices->map(function($invoice) use ($today) {
            $dueDate = Carbon::parse($invoice->due_date);
            $invoiceDate = Carbon::parse($invoice->invoice_date);
            $paymentType = optional($invoice->order)->payment_type === 'cash' ? 'Cash' : 'Kredit';
            $termDays = $invoice->order->payment_term_days ?? 7;
            
            // Hitung umur tagihan (dari tanggal invoice sampai sekarang)
            $umur = $invoiceDate->diffInDays($today);
            
            // Tentukan status badge
            if ($dueDate->lt($today)) {
                $invoice->badge_status = 'danger';
                $invoice->badge_label = 'Terlambat (' . $paymentType . ')';
            } elseif ($dueDate->isSameDay($today)) {
                $invoice->badge_status = 'warning';
                $invoice->badge_label = 'Jatuh tempo';
            } else {
                $invoice->badge_status = 'success';
                $invoice->badge_label = $paymentType . ' (' . $termDays . 'h)';
            }
            
            $invoice->umur_hari = $umur;
            
            return $invoice;
        });

        return view('sales.tagihan.index', compact(
            'invoices',
            'totalPiutang',
            'jatuhTempoHariIni',
            'lewatJatuhTempo',
            'tertagihBulanIni'
        ));
    }
}
