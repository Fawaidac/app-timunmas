<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = SalesOrder::with(['customer', 'items', 'invoice.payments'])
            ->orderBy('order_date', 'desc')
            ->get();

        return view('admin.order.index', compact('orders'));
    }

    public function show($id)
    {
        $orders = SalesOrder::with(['customer', 'sales', 'items.product', 'visit', 'invoice.payments'])
            ->findOrFail($id);

        return view('admin.order.show', compact('orders'));
    }
}
