<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Stok/barang untuk sales -> BARANG + stok dari MUTASI_BARANG (QTY_AKHIR).
 */
class StockController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::orderBy('NM_BRG');

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(NM_BRG) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(KD_BRG) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(JNS_BRG) LIKE ?', ["%{$search}%"]);
            });
        }

        $products = $query->paginate(12)->withQueryString();

        return view('sales.barang.index', compact('products'));
    }

    public function show($kdBrg)
    {
        $product = Product::where('KD_BRG', $kdBrg)->firstOrFail();

        return view('sales.barang.show', compact('product'));
    }
}