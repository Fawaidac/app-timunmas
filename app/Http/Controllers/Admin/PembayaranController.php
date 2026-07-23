<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    public function index()
    {
        // Get all payments dengan stats
        $payments = Payment::with(['invoice', 'customer', 'sales'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung stats
        $pendingApproval = $payments->where('status', 'pending_approval')->count();
        $approved = $payments->where('status', 'approved')->count();
        $rejected = $payments->where('status', 'rejected')->count();

        $totalPending = $payments->where('status', 'pending_approval')->sum('amount_paid');
        $totalApproved = $payments->where('status', 'approved')->sum('amount_paid');
        
        // Total approved bulan ini
        $approvedBulanIni = $payments->filter(function($payment) {
            return $payment->status == 'approved' 
                && Carbon::parse($payment->approved_at)->isCurrentMonth();
        })->sum('amount_paid');

        // Group by status untuk tampilan
        $paymentsByStatus = [
            'pending_approval' => $payments->where('status', 'pending_approval'),
            'approved' => $payments->where('status', 'approved'),
            'rejected' => $payments->where('status', 'rejected'),
        ];

        return view('admin.pembayaran.index', compact(
            'payments',
            'pendingApproval',
            'approved',
            'rejected',
            'totalPending',
            'totalApproved',
            'approvedBulanIni',
            'paymentsByStatus'
        ));
    }

    public function show($id)
    {
        $payment = Payment::with(['invoice.order.items.product', 'customer', 'sales', 'approver'])
            ->findOrFail($id);

        return view('admin.pembayaran.show', compact('payment'));
    }

    public function approve(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status != 'pending_approval') {
            return redirect()->back()->with('error', 'Pembayaran ini sudah diproses.');
        }

        DB::transaction(function () use ($payment) {
            // Update payment status
            $payment->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            // Update invoice remaining balance
            $invoice = $payment->invoice;
            $newBalance = $invoice->remaining_balance - $payment->amount_paid;
            
            $invoice->remaining_balance = max(0, $newBalance);
            
            // Update invoice status
            if ($invoice->remaining_balance == 0) {
                $invoice->status = 'paid';
            } elseif ($invoice->remaining_balance < $invoice->total_amount) {
                $invoice->status = 'partially_paid';
            }
            
            $invoice->save();

            // Update sales order status to approved
            if ($invoice->order) {
                $invoice->order->update([
                    'status' => 'approved',
                ]);
            }
        });

        return redirect()->route('admin.pembayaran.show', $id)
            ->with('success', 'Pembayaran berhasil diapprove.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $payment = Payment::findOrFail($id);

        if ($payment->status != 'pending_approval') {
            return redirect()->back()->with('error', 'Pembayaran ini sudah diproses.');
        }

        $payment->update([
            'status' => 'rejected',
            'rejection_reason' => $request->rejection_reason,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->route('admin.pembayaran.show', $id)
            ->with('success', 'Pembayaran berhasil ditolak.');
    }
}
