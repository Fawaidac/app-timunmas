<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $products = Product::with('warehouses')->orderBy('name')->get();
        return view('sales.barang.index', compact('products'));
    }

    public function show(Product $product)
    {
        $product->load('warehouses');
        return view('sales.barang.show', compact('product'));
    }
}
