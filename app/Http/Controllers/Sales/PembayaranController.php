<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;

class PembayaranController extends Controller
{
    public function index()
    {
        return view('sales.pembayaran.index');
    }
}
