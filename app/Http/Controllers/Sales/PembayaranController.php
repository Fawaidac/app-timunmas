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
    public function index($id)
    {
        $invoice = Invoice::with(['customer', 'payments'])->where('NO_ENT', $id)->first();
        $order = null;

        if ($invoice) {
            $customer = $invoice->customer;
        } else {
            $kdPeg = \App\Helpers\SalesHelper::kdPeg() ?: auth()->user()->KD_PEG;
            $orderQuery = SalesOrder::with(['customer', 'items', 'payments'])->where('NO_ENT', $id);
            if ($kdPeg) {
                $orderQuery->where('KD_PEG', $kdPeg);
            }
            $order = $orderQuery->firstOrFail();
            $customer = $order->customer;
            $noFaktur = $order->NO_ENT_ORD ?? $order->NO_ENT_JUAL ?? null;
            $invoice = $noFaktur ? Invoice::where('NO_ENT', $noFaktur)->first() : null;
        }

        return view('sales.pembayaran.index', compact('order', 'invoice', 'customer'));
    }

    public function store(StorePaymentRequest $request)
    {
        $payment = DB::transaction(function () use ($request) {
            $invoice = Invoice::where('NO_ENT', $request->invoice_id)->first();
            $order = null;
            if (!$invoice) {
                $order = SalesOrder::where('NO_ENT', $request->invoice_id)->firstOrFail();
            }

            $noBukti = $this->generatePaymentNumber();
            $foto = null;

            if ($request->hasFile('proof_image')) {
                $file = $request->file('proof_image');
                $filename = 'payment_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->storeAs('payment_proofs', $filename, 'public');
                $foto = 'payment_proofs/' . $filename;
            }

            $noEnt  = $invoice ? $invoice->NO_ENT : $order->NO_ENT;
            $kdCust = $invoice ? $invoice->KD_CUST : $order->KD_CUST;
            $kdPeg  = \App\Helpers\SalesHelper::kdPeg() ?: (auth()->user()->KD_PEG ?? ($invoice ? $invoice->KD_PEG : $order->KD_PEG));

            Payment::create([
                'NO_BUKTI' => $noBukti,
                'NO_ENT'   => $noEnt,
                'KD_CUST'  => $kdCust,
                'KD_PEG'   => $kdPeg,
                'TANGGAL'  => now()->format('Y-m-d H:i:s'),
                'METODE'   => $request->payment_method,
                'JUMLAH'   => (float) $request->amount_paid,
                'NO_REF'   => $request->reference_number,
                'FOTO'     => $foto,
                'STATUS'   => 'pending_approval',
                'CATATAN'  => $request->notes,
            ]);

            \App\Services\NotificationService::send([
                'type'        => 'payment_pending',
                'target_role' => 'admin',
                'title'       => 'Titip Pembayaran Baru',
                'message'     => 'Sales ' . \App\Helpers\SalesHelper::nama() . ' membuat titip pembayaran ' . $noBukti . ' (Rp ' . number_format($request->amount_paid, 0, ',', '.') . ')',
                'url'         => route('admin.payments'),
                'icon'        => '💳',
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
