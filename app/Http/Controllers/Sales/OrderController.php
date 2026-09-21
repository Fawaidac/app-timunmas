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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Sales Order -> MST_ORD_JUAL + DET_ORD_JUAL (legacy).
 * Nomor NO_ENT dibangkitkan web dengan pola desktop: OJYYMM/seri/urut,
 * urut = MAX(urut)+1 per bulan (dalam transaksi).
 */
class OrderController extends Controller
{
    private function salesKdPeg(): ?string
    {
        return auth()->user()->KD_PEG ?: null;
    }

    private function scoped()
    {
        return SalesOrder::when(
            $this->salesKdPeg(),
            fn ($q, $p) => $q->where('KD_PEG', $p),
            fn ($q) => $q->whereRaw('1=0')
        );
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

        return view('sales.order.create-standalone', compact('customers', 'products', 'selectedCustomerId', 'selectedVisitId'));
    }

    public function show($id)
    {
        $order = $this->scoped()->with(['customer', 'items.product'])
            ->where('NO_ENT', $id)->firstOrFail();

        return view('sales.order.show', compact('order'));
    }

    public function store(StoreOrderRequest $request)
    {
        // Proteksi duplikat (double-submit): order identik < 2 menit.
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
                ->with('warning', 'Order terdeteksi duplikat dan tidak disimpan ulang. Nomor order sebelumnya: ' . $duplicate->NO_ENT);
        }

        $order = DB::transaction(function () use ($request, $total) {
            $tanggal = \Carbon\Carbon::parse($request->order_date);
            $noEnt = $this->generateNoEnt($tanggal);
            $gudang = Warehouse::query()->orderBy('NM_GUDANG')->value('NM_GUDANG');

            SalesOrder::create([
                'NO_ENT'    => $noEnt,
                'TANGGAL'   => $tanggal->format('Y-m-d H:i:s'),
                'TGL_HARGA' => $tanggal->format('Y-m-d H:i:s'),
                'RNC_TGL_KIRIM' => $tanggal->format('Y-m-d H:i:s'),
                'TGL_EXP'   => $tanggal->addDays(7)->format('Y-m-d H:i:s'),
                'KD_CUST'   => $request->customer_id,
                'TOTAL'     => $total,
                'KD_PEG'    => $this->salesKdPeg(),
                'KD_USER'   => mb_substr((string) auth()->user()->NM_USER, 0, 32),
                'ST_JADI'   => 'OS',
                'JNS_BYR'   => $request->payment_type === 'cash' ? 'TUNAI' : 'KREDIT',
                'TOP'       => $request->payment_type === 'cash' ? 0 : (int) ($request->payment_term_days ?? 7),
                'GUDANG'    => $gudang,
            ]);

            foreach ($request->product_id as $index => $kdBrg) {
                $product = Product::find($kdBrg);
                $sub = (float) $request->quantity[$index] * (float) $request->price[$index];

                OrderItem::create([
                    'NO_ENT'    => $noEnt,
                    'NMR'       => $index + 1,
                    'KD_BRG'    => $kdBrg,
                    'NM_BRG'    => mb_substr((string) $product->NM_BRG, 0, 50),
                    'SATUAN'    => mb_substr((string) ($request->unit[$index] ?? $product->unit), 0, 5),
                    'JUMLAH'    => (float) $request->quantity[$index],
                    'HARGA'     => (float) $request->price[$index],
                    'TOTAL'     => $sub,
                    'SUB_TOTAL' => $sub,
                    'GUDANG'    => $gudang,
                ]);
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

        if ($request->visit_id) {
            return redirect()->route('sales.kunjungan.show', $request->visit_id)
                ->with('success', 'Sales Order berhasil dibuat. Nomor: ' . $order);
        }

        return redirect()->route('sales.order.show', $order)
            ->with('success', 'Sales Order berhasil dibuat. Nomor: ' . $order);
    }

    /**
     * Nomor order pola desktop: OJYYMM/seri/urut (urut = MAX+1 per bulan).
     * Contoh terverifikasi: OJ2608/001/00027.
     */
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
