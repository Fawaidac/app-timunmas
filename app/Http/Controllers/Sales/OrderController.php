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
        $order = $this->scoped()->with(['customer', 'items.product'])
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
                'JNS_BYR'   => 'KREDIT',
                'TOP'       => 7,
                'GUDANG'    => $gudang,
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
                    'JML_KIRIM' => 0,
                    'SAK'       => 0,
                    'HPP'       => 0,
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

        \App\Services\NotificationService::send([
            'type'        => 'new_order',
            'target_role' => 'admin',
            'title'       => 'Sales Order Baru',
            'message'     => 'Sales ' . \App\Helpers\SalesHelper::nama() . ' membuat order baru ' . $order . ' (Total: Rp ' . number_format($total, 0, ',', '.') . ')',
            'url'         => route('admin.order.show', $order),
            'icon'        => '📦',
        ]);

        if ($request->visit_id) {
            return redirect()->route('sales.kunjungan.show', $request->visit_id)
                ->with('success', 'Sales Order berhasil dibuat. Nomor: ' . $order);
        }

        return redirect()->route('sales.order.show', $order)
            ->with('success', 'Sales Order berhasil dibuat. Nomor: ' . $order);
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
