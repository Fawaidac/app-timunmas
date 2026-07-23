<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;

class LaporanController extends Controller
{
    public function index()
    {
        return view('sales.laporan.index');
    }
}
