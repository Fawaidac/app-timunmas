<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWarehouseRequest;
use App\Http\Requests\Admin\UpdateWarehouseRequest;
use App\Models\Warehouse;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        return view('admin.gudang.index', compact('warehouses'));
    }

    public function create()
    {
        return view('admin.gudang.create');
    }

    public function store(StoreWarehouseRequest $request)
    {
        Warehouse::create($request->validated());
        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Gudang berhasil ditambahkan.');
    }

    public function show(Warehouse $warehouse)
    {
        return view('admin.gudang.show', compact('warehouse'));
    }

    public function edit(Warehouse $warehouse)
    {
        return view('admin.gudang.edit', compact('warehouse'));
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse)
    {
        $warehouse->update($request->validated());

        return redirect()->route('admin.warehouses.show', $warehouse->id)
            ->with('success', 'Gudang berhasil diperbarui.');
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();
        return redirect()->route('admin.warehouses.index')
            ->with('success', 'Gudang berhasil dihapus.');
    }
}
