<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StorePaymentRequest;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;

/**
 * Titip pembayaran (web) -> tabel PAYMENT (baru), status pending_approval.
 * Route /pembayaran/{orderId}: orderId = NO_ENT order (MST_ORD_JUAL);
 * faktur terkait dicari lewat MST_ORD_JUAL.NO_ENT_JUAL.
 */
class PembayaranController extends Controller
{
    public function index($orderId)
    {
        $order = SalesOrder::with('customer')
            ->where('KD_PEG', auth()->user()->KD_PEG)
            ->where('NO_ENT', $orderId)
            ->firstOrFail();

        $noFaktur = $order->NO_ENT_JUAL;
        $invoice = $noFaktur ? Invoice::where('NO_ENT', $noFaktur)->first() : null;

        return view('sales.pembayaran.index', compact('order', 'invoice'));
    }

    public function store(StorePaymentRequest $request)
    {
        $payment = DB::transaction(function () use ($request) {
            $invoice = Invoice::where('NO_ENT', $request->invoice_id)->firstOrFail();

            $noBukti = $this->generatePaymentNumber();
            $foto = null;

            if ($request->hasFile('proof_image')) {
                $file = $request->file('proof_image');
                $filename = 'payment_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('payment_proofs', $filename, 'public');
                $foto = 'payment_proofs/' . $filename;
            }

            Payment::create([
                'NO_BUKTI' => $noBukti,
                'NO_ENT'   => $invoice->NO_ENT,
                'KD_CUST'  => $invoice->KD_CUST,
                'KD_PEG'   => auth()->user()->KD_PEG,
                'TANGGAL'  => now()->format('Y-m-d H:i:s'),
                'METODE'   => $request->payment_method,
                'JUMLAH'   => (float) $request->amount_paid,
                'NO_REF'   => $request->reference_number,
                'FOTO'     => $foto,
                'STATUS'   => 'pending_approval',
                'CATATAN'  => $request->notes,
            ]);

            return $noBukti;
        });

        return redirect()->route('sales.tagihan.index')
            ->with('success', 'Pembayaran berhasil dititipkan dan menunggu approval. Nomor: ' . $payment);
    }

    private function generatePaymentNumber(): string
    {
        $prefix = 'PAY' . date('Ymd');
        $last = (string) (Payment::query()
            ->where('NO_BUKTI', 'like', $prefix . '%')
            ->orderBy('NO_BUKTI', 'desc')
            ->value('NO_BUKTI') ?? '');

        $urut = ((int) substr($last, -4) ?: 0) + 1;

        return $prefix . str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }
}
