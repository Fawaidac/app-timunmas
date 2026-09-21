<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWarehouseRequest;
use App\Http\Requests\Admin\UpdateWarehouseRequest;
use App\Models\Warehouse;

/**
 * CRUD gudang -> tabel GUDANG (legacy).
 * PK NM_GUDANG (string) — nama tidak boleh diubah setelah create (dipakai FK).
 */
class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::orderBy('NM_GUDANG')->paginate(8)->withQueryString();

        return view('admin.gudang.index', compact('warehouses'));
    }

    public function create()
    {
        return view('admin.gudang.create');
    }

    public function store(StoreWarehouseRequest $request)
    {
        $data = $request->validated();
        $nmGudang = !empty($data['name']) ? $data['name'] : (!empty($data['code']) ? $data['code'] : Warehouse::nextName());

        Warehouse::create([
            'NM_GUDANG' => mb_substr(trim($nmGudang), 0, 50),
            'KET'       => isset($data['address']) ? mb_substr(trim($data['address']), 0, 100) : null,
        ]);

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Gudang berhasil ditambahkan.');
    }

    public function show($nmGudang)
    {
        $warehouse = Warehouse::where('NM_GUDANG', $nmGudang)->firstOrFail();

        return view('admin.gudang.show', compact('warehouse'));
    }

    public function edit($nmGudang)
    {
        $warehouse = Warehouse::where('NM_GUDANG', $nmGudang)->firstOrFail();

        return view('admin.gudang.edit', compact('warehouse'));
    }

    public function update(UpdateWarehouseRequest $request, $nmGudang)
    {
        $warehouse = Warehouse::where('NM_GUDANG', $nmGudang)->firstOrFail();
        $data = $request->validated();
        $newName = !empty($data['name']) ? mb_substr(trim($data['name']), 0, 50) : $nmGudang;
        $address = isset($data['address']) ? mb_substr(trim($data['address']), 0, 100) : null;

        // Jika nama gudang diubah
        if ($newName !== $nmGudang) {
            $exists = Warehouse::where('NM_GUDANG', $newName)->exists();
            if ($exists) {
                return redirect()->back()->withInput()->withErrors(['name' => 'Nama gudang sudah digunakan.']);
            }

            \Illuminate\Support\Facades\DB::transaction(function () use ($nmGudang, $newName, $address) {
                // Update tabel referensi
                \Illuminate\Support\Facades\DB::table('MUTASI_BARANG')->where('GUDANG', $nmGudang)->update(['GUDANG' => $newName]);
                \Illuminate\Support\Facades\DB::table('MST_ORD_JUAL')->where('GUDANG', $nmGudang)->update(['GUDANG' => $newName]);
                \Illuminate\Support\Facades\DB::table('DET_ORD_JUAL')->where('GUDANG', $nmGudang)->update(['GUDANG' => $newName]);

                // Update data gudang
                \Illuminate\Support\Facades\DB::table('GUDANG')->where('NM_GUDANG', $nmGudang)->update([
                    'NM_GUDANG' => $newName,
                    'KET'       => $address,
                ]);
            });

            return redirect()->route('admin.warehouses.show', $newName)
                ->with('success', 'Gudang berhasil diperbarui.');
        }

        $warehouse->update(['KET' => $address]);

        return redirect()->route('admin.warehouses.show', $warehouse->NM_GUDANG)
            ->with('success', 'Gudang berhasil diperbarui.');
    }

    public function destroy($nmGudang)
    {
        $warehouse = Warehouse::where('NM_GUDANG', $nmGudang)->firstOrFail();

        // 1. Cek apakah ada stok fisik (QTY_AKHIR > 0)
        $adaStokFisik = \App\Models\WarehouseStock::where('GUDANG', $nmGudang)
            ->where('QTY_AKHIR', '>', 0)
            ->exists();

        if ($adaStokFisik) {
            return redirect()->route('admin.warehouses.index')
                ->with('error', 'Gudang masih memiliki sisa stok barang aktif dan tidak dapat dihapus.');
        }

        // 2. Cek apakah pernah dipakai di riwayat transaksi order/penjualan
        $dipakai = \App\Models\SalesOrder::where('GUDANG', $nmGudang)->exists()
            || \App\Models\OrderItem::where('GUDANG', $nmGudang)->exists()
            || \Illuminate\Support\Facades\DB::table('DET_JUAL')->where('GUDANG', $nmGudang)->exists();

        if ($dipakai) {
            return redirect()->route('admin.warehouses.index')
                ->with('error', 'Gudang memiliki riwayat transaksi dan tidak dapat dihapus.');
        }

        // 3. Hapus entri stok kosong (0 qty) dan gudang dalam transaksi
        \Illuminate\Support\Facades\DB::transaction(function () use ($warehouse, $nmGudang) {
            \App\Models\WarehouseStock::where('GUDANG', $nmGudang)->delete();
            $warehouse->delete();
        });

        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Gudang berhasil dihapus.');
    }
}
