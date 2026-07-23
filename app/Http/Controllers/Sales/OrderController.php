<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreOrderRequest;
use App\Models\SalesOrder;
use App\Models\SalesVisit;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = SalesOrder::with(['customer', 'items'])
            ->where('sales_id', auth()->id())
            ->orderBy('order_date', 'desc')
            ->get();

        return view('sales.order.index', compact('orders'));
    }

    public function create()
    {
        $customers = \App\Models\Customer::orderBy('name')->get();
        $products = \App\Models\Product::with('warehouses')->orderBy('name')->get();

        return view('sales.order.create-standalone', compact('customers', 'products'));
    }

    public function store(StoreOrderRequest $request)
    {
        $order = DB::transaction(function () use ($request) {
            // Generate nomor order
            $orderNumber = $this->generateOrderNumber();

            // Hitung total
            $total = 0;
            foreach ($request->product_id as $index => $productId) {
                $qty = $request->quantity[$index];
                $price = $request->price[$index];
                $total += $qty * $price;
            }

            // Buat order
            $order = SalesOrder::create([
                'order_number'      => $orderNumber,
                'visit_id'          => $request->visit_id ?? null,
                'customer_id'       => $request->customer_id,
                'sales_id'          => auth()->id(),
                'order_date'        => $request->order_date,
                'payment_type'      => $request->payment_type,
                'payment_term_days' => $request->payment_type === 'credit' ? $request->payment_term_days : 7,
                'total_amount'      => $total,
                'status'            => 'pending',
            ]);

            // Buat order items
            foreach ($request->product_id as $index => $productId) {
                OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $productId,
                    'quantity'       => $request->quantity[$index],
                    'price_per_unit' => $request->price[$index],
                    'subtotal'       => $request->quantity[$index] * $request->price[$index],
                ]);
            }

            // Update visit jika dari kunjungan
            if ($request->visit_id) {
                $visit = SalesVisit::find($request->visit_id);
                if ($visit) {
                    $visit->update(['status' => 'completed']);
                }
            }

            return $order;
        });

        if ($request->visit_id) {
            return redirect()->route('sales.kunjungan.show', $request->visit_id)
                ->with('success', 'Sales Order berhasil dibuat. Nomor: ' . $order->order_number);
        }

        return redirect()->route('sales.order.show', $order->id)
            ->with('success', 'Sales Order berhasil dibuat. Nomor: ' . $order->order_number);
    }

    public function show($id)
    {
        $order = SalesOrder::with(['customer', 'sales', 'items.product', 'visit'])
            ->where('sales_id', auth()->id())
            ->findOrFail($id);

        return view('sales.order.show', compact('order'));
    }

    private function generateOrderNumber()
    {
        $prefix = 'SO';
        $date = date('Ymd');
        $latest = SalesOrder::where('order_number', 'like', $prefix . $date . '%')
            ->orderBy('order_number', 'desc')
            ->first();

        if ($latest) {
            $lastNumber = (int) substr($latest->order_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . $date . $newNumber;
    }
}
