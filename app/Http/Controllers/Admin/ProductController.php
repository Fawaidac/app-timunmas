<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('warehouses')->orderBy('name')->get();
        return view('admin.barang.index', compact('products'));
    }

    public function create()
    {
        $warehouses = Warehouse::orderBy('name')->get();
        return view('admin.barang.create', compact('warehouses'));
    }

    public function store(StoreProductRequest $request)
    {
        $product = DB::transaction(function () use ($request) {
            $product = Product::create($request->only([
                'sku', 'name', 'category', 'price', 'unit',
            ]));

            $product->warehouses()->attach($request->warehouse_id, [
                'stock_quantity' => $request->stock_quantity,
            ]);

            return $product;
        });

        return redirect()->route('admin.products.show', $product->id)
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    public function show(Product $product)
    {
        $product->load('warehouses');
        return view('admin.barang.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $product->load('warehouses');
        $warehouses = Warehouse::orderBy('name')->get();
        $selectedStock = $product->warehouses->first();

        return view('admin.barang.edit', compact('product', 'warehouses', 'selectedStock'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        DB::transaction(function () use ($request, $product) {
            $product->update($request->only([
                'name', 'category', 'price', 'unit',
            ]));

            $product->warehouses()->sync([
                $request->warehouse_id => [
                    'stock_quantity' => $request->stock_quantity,
                ],
            ]);
        });

        return redirect()->route('admin.products.show', $product->id)
            ->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')
            ->with('success', 'Barang berhasil dihapus.');
    }
}
