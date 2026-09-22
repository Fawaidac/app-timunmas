<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::orderBy('NM_BRG');

        if ($request->filled('search')) {
            $search = strtoupper(trim($request->search));
            $term = "%{$search}%";
            $query->where(function ($q) use ($term) {
                $q->whereRaw("UPPER(CAST(KD_BRG AS VARCHAR(100))) LIKE ?", [$term])
                  ->orWhereRaw("UPPER(CAST(NM_BRG AS VARCHAR(100))) LIKE ?", [$term])
                  ->orWhereRaw("UPPER(CAST(COALESCE(JNS_BRG, '') AS VARCHAR(100))) LIKE ?", [$term])
                  ->orWhereRaw("UPPER(CAST(COALESCE(NM_SUPPL, '') AS VARCHAR(100))) LIKE ?", [$term])
                  ->orWhereRaw("UPPER(CAST(COALESCE(RAK, '') AS VARCHAR(100))) LIKE ?", [$term]);
            });
        }

        if ($request->filled('kategori')) {
            $query->where('JNS_BRG', $request->kategori);
        }

        if ($request->filled('supplier')) {
            $query->where('KD_SUPPL', $request->supplier);
        }

        if ($request->filled('status')) {
            $query->where('STS_AKTIF', $request->status);
        }

        $products = $query->paginate(12)->withQueryString();

        $kategoriList = DB::connection('firebird')->table('JNS_BARANG')->get();
        $supplierList = DB::connection('firebird')->table('SUPPLIER')->get();

        return view('admin.barang.index', compact('products', 'kategoriList', 'supplierList'));
    }

    public function create()
    {
        $nextKdBrg = Product::nextKdBrg('BRG');
        $kategoriList = DB::connection('firebird')->table('JNS_BARANG')->get();
        $supplierList = DB::connection('firebird')->table('SUPPLIER')->get();
        $warehouses = Warehouse::orderBy('NM_GUDANG')->get();

        return view('admin.barang.create', compact('nextKdBrg', 'kategoriList', 'supplierList', 'warehouses'));
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        $kdBrg = !empty($data['sku']) ? strtoupper(trim($data['sku'])) : Product::nextKdBrg('BRG');

        $nmJns = null;
        $kdJns = $data['kd_jns_brg'] ?? null;
        if (!empty($kdJns)) {
            $j = DB::connection('firebird')->table('JNS_BARANG')->where('KD_JNS_BRG', $kdJns)->first();
            $nmJns = $j ? trim($j->NM_JNS_BRG) : $kdJns;
        }

        $nmSuppl = null;
        $kdSuppl = $data['kd_suppl'] ?? null;
        if (!empty($kdSuppl)) {
            $s = DB::connection('firebird')->table('SUPPLIER')->where('KD_SUPPL', $kdSuppl)->first();
            $nmSuppl = $s ? trim($s->NM_SUPPL) : null;
        }

        $hargaJl = (float) $data['price'];
        $hargaBl = isset($data['harga_bl']) ? (float) $data['harga_bl'] : 0;

        DB::transaction(function () use ($kdBrg, $data, $nmJns, $kdJns, $nmSuppl, $kdSuppl, $hargaJl, $hargaBl) {
            Product::create([
                'KD_BRG'      => $kdBrg,
                'NM_BRG'      => strtoupper(trim($data['name'])),
                'JNS_BRG'     => $nmJns,
                'KD_JNS_BRG'  => $kdJns,
                'KD_SUPPL'    => $kdSuppl,
                'NM_SUPPL'    => $nmSuppl,
                'HARGA_JL'    => $hargaJl,
                'HARGA_BL'    => $hargaBl,
                'HARGA_JL2'   => isset($data['harga_jl2']) ? (float) $data['harga_jl2'] : 0,
                'HARGA_JL3'   => isset($data['harga_jl3']) ? (float) $data['harga_jl3'] : 0,
                'SATUAN1'     => strtoupper(trim($data['unit'])),
                'SATUAN2'     => !empty($data['satuan2']) ? strtoupper(trim($data['satuan2'])) : null,
                'KAPASITAS2'  => isset($data['kapasitas2']) ? (float) $data['kapasitas2'] : 0,
                'SATUAN3'     => !empty($data['satuan3']) ? strtoupper(trim($data['satuan3'])) : null,
                'KAPASITAS3'  => isset($data['kapasitas3']) ? (float) $data['kapasitas3'] : 0,
                'STOK_MIN'    => isset($data['stok_min']) ? (float) $data['stok_min'] : 0,
                'RAK'         => $data['rak'] ?? null,
                'BERAT'       => isset($data['berat']) ? (float) $data['berat'] : 0,
                'STS_AKTIF'   => $data['sts_aktif'] ?? 'AKTIF',
                'HPP'         => $hargaBl,
            ]);

            if (!empty($data['warehouse_id']) && isset($data['stock_quantity'])) {
                $this->upsertStock($data['warehouse_id'], $kdBrg, (float) $data['stock_quantity'], $hargaBl);
            }
        });

        return redirect()->route('admin.products.show', $kdBrg)
            ->with('success', "Barang [{$data['name']}] berhasil ditambahkan.");
    }

    public function show($kdBrg)
    {
        $product = Product::where('KD_BRG', $kdBrg)->firstOrFail();

        return view('admin.barang.show', compact('product'));
    }

    public function edit($kdBrg)
    {
        $product = Product::where('KD_BRG', $kdBrg)->firstOrFail();
        $kategoriList = DB::connection('firebird')->table('JNS_BARANG')->get();
        $supplierList = DB::connection('firebird')->table('SUPPLIER')->get();
        $warehouses = Warehouse::orderBy('NM_GUDANG')->get();
        $selectedStock = WarehouseStock::where('KD_BRG', $kdBrg)->first();

        return view('admin.barang.edit', compact('product', 'kategoriList', 'supplierList', 'warehouses', 'selectedStock'));
    }

    public function update(UpdateProductRequest $request, $kdBrg)
    {
        $product = Product::where('KD_BRG', $kdBrg)->firstOrFail();
        $data = $request->validated();

        $nmJns = $product->JNS_BRG;
        $kdJns = $data['kd_jns_brg'] ?? $product->KD_JNS_BRG;
        if (!empty($kdJns)) {
            $j = DB::connection('firebird')->table('JNS_BARANG')->where('KD_JNS_BRG', $kdJns)->first();
            $nmJns = $j ? trim($j->NM_JNS_BRG) : $kdJns;
        }

        $nmSuppl = $product->NM_SUPPL;
        $kdSuppl = $data['kd_suppl'] ?? $product->KD_SUPPL;
        if (!empty($kdSuppl)) {
            $s = DB::connection('firebird')->table('SUPPLIER')->where('KD_SUPPL', $kdSuppl)->first();
            $nmSuppl = $s ? trim($s->NM_SUPPL) : null;
        }

        $hargaJl = (float) $data['price'];
        $hargaBl = isset($data['harga_bl']) ? (float) $data['harga_bl'] : (float) $product->HARGA_BL;

        DB::transaction(function () use ($product, $data, $nmJns, $kdJns, $nmSuppl, $kdSuppl, $hargaJl, $hargaBl) {
            $product->update([
                'NM_BRG'      => strtoupper(trim($data['name'])),
                'JNS_BRG'     => $nmJns,
                'KD_JNS_BRG'  => $kdJns,
                'KD_SUPPL'    => $kdSuppl,
                'NM_SUPPL'    => $nmSuppl,
                'HARGA_JL'    => $hargaJl,
                'HARGA_BL'    => $hargaBl,
                'HARGA_JL2'   => isset($data['harga_jl2']) ? (float) $data['harga_jl2'] : 0,
                'HARGA_JL3'   => isset($data['harga_jl3']) ? (float) $data['harga_jl3'] : 0,
                'SATUAN1'     => strtoupper(trim($data['unit'])),
                'SATUAN2'     => !empty($data['satuan2']) ? strtoupper(trim($data['satuan2'])) : null,
                'KAPASITAS2'  => isset($data['kapasitas2']) ? (float) $data['kapasitas2'] : 0,
                'SATUAN3'     => !empty($data['satuan3']) ? strtoupper(trim($data['satuan3'])) : null,
                'KAPASITAS3'  => isset($data['kapasitas3']) ? (float) $data['kapasitas3'] : 0,
                'STOK_MIN'    => isset($data['stok_min']) ? (float) $data['stok_min'] : 0,
                'RAK'         => $data['rak'] ?? null,
                'BERAT'       => isset($data['berat']) ? (float) $data['berat'] : 0,
                'STS_AKTIF'   => $data['sts_aktif'] ?? 'AKTIF',
                'HPP'         => $hargaBl,
            ]);

            if (!empty($data['warehouse_id']) && isset($data['stock_quantity'])) {
                $this->upsertStock($data['warehouse_id'], $product->KD_BRG, (float) $data['stock_quantity'], $hargaBl);
            }
        });

        return redirect()->route('admin.products.show', $product->KD_BRG)
            ->with('success', "Barang [{$product->NM_BRG}] berhasil diperbarui.");
    }

    public function destroy($kdBrg)
    {
        $product = Product::where('KD_BRG', $kdBrg)->firstOrFail();

        $dipakai = \App\Models\OrderItem::where('KD_BRG', $kdBrg)->exists()
            || DB::connection('firebird')->table('DET_ORD_JUAL')->where('KD_BRG', $kdBrg)->exists();

        if ($dipakai) {
            return redirect()->route('admin.products.index')
                ->with('error', 'Barang memiliki riwayat transaksi sales order dan tidak dapat dihapus.');
        }

        DB::transaction(function () use ($product) {
            WarehouseStock::where('KD_BRG', $product->KD_BRG)->delete();
            $product->delete();
        });

        return redirect()->route('admin.products.index')
            ->with('success', 'Barang berhasil dihapus.');
    }

    private function upsertStock(string $gudang, string $kdBrg, float $qty, float $hargaBeli = 0): void
    {
        $rp = $qty * $hargaBeli;

        $exists = DB::connection('firebird')
            ->table('MUTASI_BARANG')
            ->where('GUDANG', $gudang)
            ->where('KD_BRG', $kdBrg)
            ->exists();

        if ($exists) {
            DB::connection('firebird')
                ->table('MUTASI_BARANG')
                ->where('GUDANG', $gudang)
                ->where('KD_BRG', $kdBrg)
                ->update([
                    'QTY_AWAL' => $qty,
                    'RP_AWAL'  => $rp,
                ]);
        } else {
            DB::connection('firebird')
                ->table('MUTASI_BARANG')
                ->insert([
                    'GUDANG'    => $gudang,
                    'KD_BRG'    => $kdBrg,
                    'QTY_AWAL'  => $qty,
                    'RP_AWAL'   => $rp,
                    'QTY_MASUK' => 0,
                    'RP_MASUK'  => 0,
                    'QTY_KELUAR'=> 0,
                    'RP_KELUAR' => 0,
                ]);
        }
    }
}
