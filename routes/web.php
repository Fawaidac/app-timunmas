<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\UserController;

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Sales routes (protected)
Route::middleware(['auth', 'role:sales'])->group(function () {
    Route::view('/', 'sales.dashboard.index')->name('dashboard');
    Route::view('/kunjungan', 'sales.kunjungan.index')->name('kunjungan');
    Route::view('/check-in', 'sales.checkin.index')->name('checkin');
    Route::view('/order', 'sales.order.index')->name('order');
    Route::view('/tagihan', 'sales.tagihan.index')->name('tagihan');
    Route::view('/titip-pembayaran', 'sales.pembayaran.index')->name('pembayaran');
    Route::view('/stok', 'sales.barang.index')->name('stok');
    Route::view('/laporan', 'sales.laporan.index')->name('laporan');
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
