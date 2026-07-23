<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StorePaymentRequest;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    public function index($orderId)
    {
        $order = SalesOrder::with(['customer', 'invoice'])
            ->where('sales_id', auth()->id())
            ->findOrFail($orderId);

        if (!$order->invoice) {
            return redirect()->route('sales.order.show', $orderId)
                ->with('error', 'Order ini belum memiliki invoice.');
        }

        return view('sales.pembayaran.index', compact('order'));
    }

    public function store(StorePaymentRequest $request)
    {
        $payment = DB::transaction(function () use ($request) {
            $invoice = Invoice::with('order')->findOrFail($request->invoice_id);

            // Generate payment number
            $paymentNumber = $this->generatePaymentNumber();

            // Handle proof image upload
            $proofImageUrl = null;
            if ($request->hasFile('proof_image')) {
                $file = $request->file('proof_image');
                $filename = 'payment_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('payment_proofs', $filename, 'public');
                $proofImageUrl = 'payment_proofs/' . $filename;
            }

            // Create payment
            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'invoice_id' => $invoice->id,
                'visit_id' => $invoice->order->visit_id ?? null,
                'sales_id' => auth()->id(),
                'customer_id' => $invoice->customer_id,
                'payment_method' => $request->payment_method,
                'amount_paid' => $request->amount_paid,
                'reference_number' => $request->reference_number,
                'proof_image_url' => $proofImageUrl,
                'status' => 'pending_approval',
            ]);

            return $payment;
        });

        return redirect()->route('sales.order.show', $payment->invoice->order_id)
            ->with('success', 'Pembayaran berhasil dititipkan dan menunggu approval. Nomor: ' . $payment->payment_number);
    }

    private function generatePaymentNumber()
    {
        $prefix = 'PAY';
        $date = date('Ymd');
        $latest = Payment::where('payment_number', 'like', $prefix . $date . '%')
            ->orderBy('payment_number', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->payment_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $date . $newNumber;
    }
}
