<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;

class TagihanController extends Controller
{
    public function index()
    {
        return view('sales.tagihan.index');
    }
}
