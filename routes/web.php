<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Sales\DashboardController;
use App\Http\Controllers\Sales\VisitController;
use App\Http\Controllers\Sales\OrderController;
use App\Http\Controllers\Sales\TagihanController;
use App\Http\Controllers\Sales\PembayaranController;
use App\Http\Controllers\Sales\LaporanController;
use App\Http\Controllers\Sales\StockController;

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Sales routes (protected)
Route::middleware(['auth', 'role:sales'])->prefix('sales')->name('sales.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Kunjungan Sales
    Route::get('/kunjungan', [VisitController::class, 'index'])->name('kunjungan.index');
    Route::get('/kunjungan/create', [VisitController::class, 'create'])->name('kunjungan.create');
    Route::post('/kunjungan', [VisitController::class, 'store'])->name('kunjungan.store');
    Route::get('/kunjungan/{id}', [VisitController::class, 'show'])->name('kunjungan.show');
    Route::get('/kunjungan/{id}/edit', [VisitController::class, 'edit'])->name('kunjungan.edit');
    Route::put('/kunjungan/{id}', [VisitController::class, 'update'])->name('kunjungan.update');
    Route::delete('/kunjungan/{id}', [VisitController::class, 'destroy'])->name('kunjungan.destroy');
    Route::get('/kunjungan/{id}/checkin', [VisitController::class, 'checkin'])->name('kunjungan.checkin');
    Route::post('/kunjungan/{id}/checkin', [VisitController::class, 'storeCheckin'])->name('kunjungan.checkin.store');
    Route::get('/kunjungan/{id}/order', [VisitController::class, 'createOrder'])->name('kunjungan.order.create');
    
    // Sales Order
    Route::get('/order', [OrderController::class, 'index'])->name('order.index');
    Route::get('/order/create', [OrderController::class, 'create'])->name('order.create');
    Route::post('/order', [OrderController::class, 'store'])->name('order.store');
    Route::get('/order/{id}', [OrderController::class, 'show'])->name('order.show');
    
    // Tagihan
    Route::get('/tagihan', [TagihanController::class, 'index'])->name('tagihan.index');
    
    // Pembayaran
    Route::get('/pembayaran/{orderId}', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::post('/pembayaran', [PembayaranController::class, 'store'])->name('pembayaran.store');
    
    // Stok / Products
    Route::get('/stok', [StockController::class, 'index'])->name('stok.index');
    Route::get('/stok/{product}', [StockController::class, 'show'])->name('stok.show');
    
    // Laporan / Reports
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
});

// Admin routes (protected)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/', 'admin.dashboard.index')->name('dashboard');
    Route::view('/orders', 'admin.order.index')->name('orders');
    Route::view('/visits', 'admin.kunjungan.index')->name('visits');
    Route::view('/invoices', 'admin.tagihan.index')->name('invoices');
    Route::view('/payments', 'admin.pembayaran.index')->name('payments');
    Route::view('/reports', 'admin.laporan.index')->name('reports');

    // CRUD: Barang / Products
    Route::resource('products', ProductController::class);

    // CRUD: Customer / Pelanggan
    Route::resource('customers', CustomerController::class);

    // CRUD: Gudang / Warehouses
    Route::resource('warehouses', WarehouseController::class);

    // CRUD: Pengguna / Users
    Route::resource('users', UserController::class);
});
