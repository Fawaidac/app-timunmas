<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = SalesOrder::with(['customer', 'items'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->whereRaw('UPPER(CAST(NO_ENT AS VARCHAR(100))) LIKE ?', ['%' . strtoupper($s) . '%'])
                    ->orWhereRaw('UPPER(CAST(KD_CUST AS VARCHAR(100))) LIKE ?', ['%' . strtoupper($s) . '%']);
            })
            ->orderBy('TANGGAL', 'desc')
            ->paginate(10)->withQueryString();

        return view('admin.order.index', compact('orders'));
    }

    public function show($id)
    {
        $orders = SalesOrder::with(['customer', 'items.product'])
            ->where('NO_ENT', $id)
            ->firstOrFail();

        return view('admin.order.show', compact('orders'));
    }
}
