<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
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

        return view('sales.barang.index', compact('products', 'kategoriList', 'supplierList'));
    }

    public function show($kdBrg)
    {
        $product = Product::where('KD_BRG', $kdBrg)->firstOrFail();

        return view('sales.barang.show', compact('product'));
    }
}