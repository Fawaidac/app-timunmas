<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesOrder;
use App\Models\User;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Invoice;
use App\Models\WarehouseStock;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Stats Card 1: Total Orders
        $totalOrders = SalesOrder::count();
        $ordersLastMonth = SalesOrder::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();
        $ordersThisMonth = SalesOrder::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $ordersGrowth = $ordersLastMonth > 0 
            ? round((($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100) 
            : 0;

        // Stats Card 2: Total Revenue (dari payment yang sudah approved)
        $totalRevenue = Payment::where('status', 'approved')->sum('amount_paid');
        
        $revenueLastMonth = Payment::where('status', 'approved')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('amount_paid');
        
        $revenueThisMonth = Payment::where('status', 'approved')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount_paid');
        
        $revenueGrowth = $revenueLastMonth > 0 
            ? round((($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100) 
            : 0;

        // Stats Card 3: Sales Aktif (yang punya order hari ini)
        $totalSales = User::where('role', 'sales')->count();
        $activeSalesToday = User::where('role', 'sales')
            ->whereHas('orders', function($q) {
                $q->whereDate('created_at', today());
            })
            ->count();

        // Stats Card 4: Pending Approval
        $pendingOrders = Payment::where('status', 'pending_approval')->count();

        // Recent Orders (6 terbaru)
        $recentOrders = SalesOrder::with(['sales', 'customer'])
            ->latest()
            ->take(6)
            ->get();

        // Top Performing Sales (5 sales dengan revenue tertinggi bulan ini dari payment approved)
        $topSales = User::where('role', 'sales')
            ->withSum(['payments as total_revenue' => function($q) {
                $q->where('status', 'approved')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
            }], 'amount_paid')
            ->orderByDesc('total_revenue')
            ->take(5)
            ->get();

        // Pending Payments (invoice yang belum lunas)
        $pendingPayments = Invoice::with('customer')
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->orderBy('due_date', 'asc')
            ->take(6)
            ->get();

        // Low Stock Alert (warehouse stock < 20)
        $lowStockProducts = WarehouseStock::with(['product', 'warehouse'])
            ->where('stock_quantity', '<', 20)
            ->orderBy('stock_quantity', 'asc')
            ->take(6)
            ->get()
            ->map(function($stock) {
                return (object)[
                    'name' => $stock->product->name,
                    'stock' => $stock->stock_quantity,
                    'warehouse' => $stock->warehouse
                ];
            });

        return view('admin.dashboard.index', compact(
            'totalOrders',
            'ordersGrowth',
            'totalRevenue',
            'revenueGrowth',
            'totalSales',
            'activeSalesToday',
            'pendingOrders',
            'recentOrders',
            'topSales',
            'pendingPayments',
            'lowStockProducts'
        ));
    }
}
