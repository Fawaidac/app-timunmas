<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;

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
    Route::view('/products', 'admin.barang.index')->name('products');
    Route::view('/warehouses', 'admin.gudang.index')->name('warehouses');
    Route::view('/customers', 'admin.pelanggan.index')->name('customers');
    Route::view('/users', 'admin.pengguna.index')->name('users');
    Route::view('/reports', 'admin.laporan.index')->name('reports');
});
