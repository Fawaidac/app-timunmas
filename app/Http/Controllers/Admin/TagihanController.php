<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TagihanController extends Controller
{
    public function index()
    {
        // Get all invoices dengan stats
        $invoices = Invoice::with(['customer', 'order.sales', 'latestPayment'])
            ->orderBy('due_date', 'asc')
            ->get();

        // Hitung stats
        $totalPiutang = $invoices->sum('remaining_balance');
        
        $today = Carbon::today();
        $jatuhTempoHariIni = $invoices->filter(function($inv) use ($today) {
            return Carbon::parse($inv->due_date)->isSameDay($today);
        })->sum('remaining_balance');
        
        $lewatJatuhTempo = $invoices->filter(function($inv) use ($today) {
            return Carbon::parse($inv->due_date)->lt($today) && $inv->status != 'paid';
        })->sum('remaining_balance');

        // Total yang sudah tertagih (approved payment) bulan ini
        $tertagihBulanIni = Payment::where('status', 'approved')
            ->whereMonth('approved_at', Carbon::now()->month)
            ->whereYear('approved_at', Carbon::now()->year)
            ->sum('amount_paid');

        // Total invoice berdasarkan status
        $invoiceByStatus = [
            'unpaid' => $invoices->where('status', 'unpaid')->count(),
            'partially_paid' => $invoices->where('status', 'partially_paid')->count(),
            'paid' => $invoices->where('status', 'paid')->count(),
            'overdue' => $invoices->where('status', 'overdue')->count(),
        ];

        // Format invoices dengan umur dan badge
        $invoices = $invoices->map(function($invoice) use ($today) {
            $dueDate = Carbon::parse($invoice->due_date);
            $invoiceDate = Carbon::parse($invoice->invoice_date);
            
            // Hitung umur tagihan
            $umur = $invoiceDate->diffInDays($today);
            
            // Tentukan status badge
            if ($invoice->status == 'paid') {
                $invoice->badge_status = 'success';
                $invoice->badge_label = 'Lunas';
            } elseif ($dueDate->lt($today)) {
                $invoice->badge_status = 'danger';
                $invoice->badge_label = 'Terlambat';
            } elseif ($dueDate->isSameDay($today)) {
                $invoice->badge_status = 'warning';
                $invoice->badge_label = 'Jatuh tempo hari ini';
            } else {
                $invoice->badge_status = 'info';
                $invoice->badge_label = 'Belum jatuh tempo';
            }
            
            $invoice->umur_hari = $umur;
            
            return $invoice;
        });

        return view('admin.tagihan.index', compact(
            'invoices',
            'totalPiutang',
            'jatuhTempoHariIni',
            'lewatJatuhTempo',
            'tertagihBulanIni',
            'invoiceByStatus'
        ));
    }
}
