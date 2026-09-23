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
            ->when($request->filled('payment_type'), function ($q) use ($request) {
                $q->where('JNS_BYR', strtoupper($request->payment_type));
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = $request->search;
                $q->where(function ($sub) use ($s) {
                    $sub->whereRaw('UPPER(CAST(NO_ENT AS VARCHAR(100))) LIKE ?', ['%' . strtoupper($s) . '%'])
                        ->orWhereRaw('UPPER(CAST(KD_CUST AS VARCHAR(100))) LIKE ?', ['%' . strtoupper($s) . '%']);
                });
            })
            ->orderBy('TANGGAL', 'desc')
            ->paginate(10)->withQueryString();

        $tunaiCount = SalesOrder::where('JNS_BYR', 'TUNAI')->count();
        $kreditCount = SalesOrder::where('JNS_BYR', 'KREDIT')->count();

        return view('admin.order.index', compact('orders', 'tunaiCount', 'kreditCount'));
    }

    public function show($id)
    {
        $order = SalesOrder::with(['customer', 'items.product.stocks'])
            ->where('NO_ENT', $id)
            ->firstOrFail();

        return view('admin.order.show', compact('order'));
    }

}
