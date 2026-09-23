<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreOrderRequest;
use App\Models\SalesOrder;
use App\Models\SalesVisit;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private function salesKdPeg(): ?string
    {
        return auth()->user()->KD_PEG ?: null;
    }

    private function salesKdUser(): string
    {
        return (string) auth()->user()->NM_USER;
    }

    private function scoped()
    {
        $kdPeg = $this->salesKdPeg();
        if ($kdPeg) {
            return SalesOrder::where('KD_PEG', $kdPeg);
        }
        return SalesOrder::where('KD_USER', $this->salesKdUser());
    }

    public function index(Request $request)
    {
        $orders = $this->scoped()->with(['customer', 'items'])
            ->when($request->filled('customer_id'), fn ($q) => $q->where('KD_CUST', $request->customer_id))
            ->orderBy('TANGGAL', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('sales.order.index', compact('orders'));
    }

    public function create(Request $request)
    {
        $customers = Customer::orderBy('NM_CUST')->get();
        $products = Product::orderBy('NM_BRG')->get();

        $selectedCustomerId = $request->query('customer_id');
        $selectedVisitId = $request->query('visit_id');

        $selectedCustomer = $selectedCustomerId 
            ? ($customers->firstWhere('id', $selectedCustomerId) ?? $customers->firstWhere('KD_CUST', $selectedCustomerId) ?? Customer::find($selectedCustomerId))
            : null;

        return view('sales.order.create-standalone', compact('customers', 'products', 'selectedCustomerId', 'selectedVisitId', 'selectedCustomer'));
    }

    public function show($id)
    {
        $order = $this->scoped()->with(['customer', 'items.product', 'payments'])
            ->where('NO_ENT', $id)->firstOrFail();

        return view('sales.order.show', compact('order'));
    }

    public function store(StoreOrderRequest $request)
    {
        $total = 0;
        foreach ($request->product_id as $i => $pid) {
            $total += (float) $request->quantity[$i] * (float) $request->price[$i];
        }

        $duplicate = $this->scoped()
            ->where('KD_CUST', $request->customer_id)
            ->where('TOTAL', $total)
            ->where('TANGGAL', $request->order_date)
            ->first();

        if ($duplicate) {
            return redirect()
                ->route($request->visit_id ? 'sales.kunjungan.show' : 'sales.order.show', $request->visit_id ?? $duplicate->NO_ENT)
                ->with('warning', 'Transaksi terdeteksi duplikat dan tidak disimpan ulang. Nomor nota sebelumnya: ' . $duplicate->NO_ENT);
        }

        $jnsByr = strtoupper($request->input('payment_type', 'KREDIT')) === 'TUNAI' ? 'TUNAI' : 'KREDIT';
        $payMethod = $jnsByr === 'TUNAI'
            ? (strtoupper((string) $request->input('payment_method')) === 'TRANSFER' ? 'transfer' : 'cash')
            : null;

        $order = DB::transaction(function () use ($request, $total, $jnsByr) {
            $tanggal = \Carbon\Carbon::parse($request->order_date);
            $noEnt = $this->generateNoEnt($tanggal);
            $gudang = Warehouse::query()->orderBy('NM_GUDANG')->value('NM_GUDANG');
            $topDays = ($jnsByr === 'TUNAI') ? 0 : 7;
            $tglExp = ($jnsByr === 'TUNAI') ? $tanggal->format('Y-m-d H:i:s') : $tanggal->copy()->addDays(7)->format('Y-m-d H:i:s');

            // 1. Simpan Sales Order / Nota Canvass.
            //    Catatan trigger legacy MST_ORD_JUAL_BI (BEFORE INSERT):
            //    - ST_JADI dipaksa 'OS' oleh trigger (semantik legacy: OS = order terbuka,
            //      INV diset nanti saat jadi faktur) — konsisten dengan TagihanController
            //      dan Customer::orderDebt yang memfilter ST_JADI = 'OS'.
            //    - JNS_BYR dipaksa 'KREDIT' oleh trigger → dikoreksi via UPDATE setelah item.
            SalesOrder::create([
                'NO_ENT'        => $noEnt,
                'TANGGAL'       => $tanggal->format('Y-m-d H:i:s'),
                'TGL_HARGA'     => $tanggal->format('Y-m-d H:i:s'),
                'RNC_TGL_KIRIM' => $tanggal->format('Y-m-d H:i:s'),
                'TGL_EXP'       => $tglExp,
                'KD_CUST'       => $request->customer_id,
                'TOTAL'         => $total,
                'KD_PEG'        => $this->salesKdPeg(),
                'KD_USER'       => mb_substr((string) auth()->user()->NM_USER, 0, 32),
                'ST_JADI'       => 'INV',
                'JNS_BYR'       => $jnsByr,
                'TOP'           => $topDays,
                'GUDANG'        => $gudang,
            ]);

            $maxNomor = (int) (DB::connection('firebird')->table('DET_ORD_JUAL')->max('NOMOR') ?? 0);
            foreach ($request->product_id as $index => $kdBrg) {
                $product = Product::find($kdBrg);
                $qty = (float) $request->quantity[$index];
                $price = (float) $request->price[$index];
                $sub = $qty * $price;
                $maxNomor++;

                $satuan = $request->unit[$index] ?? $product->unit ?? 'PCS';
                $satKe = (int) ($request->sat_ke[$index] ?? 1);
                $kapasitas = (float) ($request->kapasitas[$index] ?? 1);
                if ($kapasitas <= 0) {
                    $kapasitas = 1;
                }
                $jmlTur = (float) ($qty * $kapasitas);

                OrderItem::create([
                    'NOMOR'     => $maxNomor,
                    'NO_ENT'    => $noEnt,
                    'NMR'       => $index + 1,
                    'KD_BRG'    => $kdBrg,
                    'KD_BRG_1'  => $kdBrg,
                    'NM_BRG'    => mb_substr((string) ($product->NM_BRG ?? ''), 0, 50),
                    'SATUAN'    => mb_substr((string) $satuan, 0, 5),
                    'SAT_KE'    => $satKe,
                    'JUMLAH'    => $qty,
                    'HARGA'     => $price,
                    'DISC1'     => 0,
                    'DISC2'     => 0,
                    'DISC_RP'   => 0,
                    'TOTAL'     => $sub,
                    'SUB_TOTAL' => $sub,
                    'JML_TUR'   => $jmlTur,
                    'JML_KIRIM' => $qty,
                    'SAK'       => 0,
                    'HPP'       => 0,
                ]);
            }

            // Trigger legacy hanya BEFORE INSERT:
            // - MST_ORD_JUAL_BI memaksa JNS_BYR = 'KREDIT' sehingga pilihan Tunai dari form selalu tertimpa.
            // - DET_ORD_JUAL_AI mengakumulasi TOTAL header (TOTAL = TOTAL + SUB_TOTAL per item),
            //   sehingga header yang sudah diisi $total menjadi dobel.
            // UPDATE tidak memicu trigger BEFORE INSERT, jadi kita koreksi nilainya di sini.
            SalesOrder::where('NO_ENT', $noEnt)->update([
                'JNS_BYR' => $jnsByr,
                'TOP'     => $topDays,
                'TOTAL'   => $total,
            ]);

            // 3. Order LUNAS: catat pembayaran langsung APPROVED (tanpa approval admin),
            //    metode Cash/Transfer sesuai pilihan form. Hanya mencatat ke tabel PAYMENT —
            //    TIDAK menyentuh MST_JUAL (aturan: order = MST_ORD_JUAL/DET_ORD_JUAL;
            //    MST_JUAL khusus FAKTUR hasil konversi).
            if ($jnsByr === 'TUNAI') {
                Payment::create([
                    'NO_BUKTI'        => $this->generatePaymentNumber(),
                    'NO_ENT'          => $noEnt,
                    'KD_CUST'         => $request->customer_id,
                    'KD_PEG'          => $this->salesKdPeg(),
                    'TANGGAL'         => now()->format('Y-m-d H:i:s'),
                    'METODE'          => $payMethod,
                    'JUMLAH'          => $total,
                    'STATUS'          => 'approved',
                    'NO_USER_APPROVE' => auth()->user()->NO_USER,
                    'TGL_APPROVE'     => now()->format('Y-m-d H:i:s'),
                    'CATATAN'         => 'Pelunasan saat order dibuat (langsung lunas, tanpa approval).',
                ]);
            }

            // 4. Jika KREDIT, piutang dicatat di CUSTOMER.JML_PIUTANG
            //    (order = MST_ORD_JUAL — bukan MST_JUAL), tempo 7 hari, lalu masuk Tagihan.
            if ($jnsByr === 'KREDIT') {
                $cust = Customer::find($request->customer_id);
                if ($cust) {
                    $currentPiutang = (float) ($cust->JML_PIUTANG ?? 0);
                    $cust->update([
                        'JML_PIUTANG' => $currentPiutang + $total,
                    ]);
                }
            }

            // Update kunjungan jika order dibuat dari kunjungan
            if ($request->visit_id) {
                $visit = SalesVisit::find($request->visit_id);
                if ($visit) {
                    $visit->update(['STATUS' => 'completed', 'NO_ENT_ORD' => $noEnt]);
                }
            }

            return $noEnt;
        });

        $msgText = ($jnsByr === 'TUNAI')
            ? 'Nota Penjualan Lunas (' . ($payMethod === 'transfer' ? 'Transfer' : 'Cash') . ') berhasil dibuat. Nomor: ' . $order
            : 'Nota Penjualan Kredit (Tempo 7 Hari) berhasil dibuat, dicatat ke Piutang Customer & masuk Tagihan. Nomor: ' . $order;

        if ($request->visit_id) {
            return redirect()->route('sales.kunjungan.show', $request->visit_id)
                ->with('success', $msgText);
        }

        return redirect()->route('sales.order.show', $order)
            ->with('success', $msgText);
    }

    public function submitOrder($id)
    {
        $order = $this->scoped()->where('NO_ENT', $id)->firstOrFail();

        if ($order->ST_JADI !== 'QUO' && $order->ST_JADI !== 'DRAFT') {
            return redirect()->back()->with('error', 'Hanya draf penawaran yang bisa diajukan sebagai Sales Order.');
        }

        $order->update(['ST_JADI' => 'OS']);

        \App\Services\NotificationService::send([
            'type'        => 'new_order',
            'target_role' => 'admin',
            'title'       => 'Sales Order Baru (Pending Approval)',
            'message'     => 'Sales ' . \App\Helpers\SalesHelper::nama() . ' mengajukan Penawaran ' . $order->NO_ENT . ' menjadi Sales Order (Total: Rp ' . number_format($order->TOTAL, 0, ',', '.') . ')',
            'url'         => route('admin.order.show', $order->NO_ENT),
            'icon'        => '📦',
        ]);

        return redirect()->route('sales.order.show', $order->NO_ENT)
            ->with('success', 'Penawaran berhasil diajukan menjadi Sales Order (Pending Approval Admin).');
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

    private function generateNoEnt(\Carbon\Carbon $tanggal): string
    {
        $prefix = 'OJ' . $tanggal->format('ym') . '/001/';
        $last = (string) (SalesOrder::query()
            ->where('NO_ENT', 'like', $prefix . '%')
            ->orderBy('NO_ENT', 'desc')
            ->value('NO_ENT') ?? '');

        $urut = ((int) substr($last, -5) ?: 0) + 1;

        return $prefix . str_pad((string) $urut, 5, '0', STR_PAD_LEFT);
    }
}
