<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesVisit;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Kunjungan (admin) -> tabel KUNJUNGAN (web).
 */
class VisitController extends Controller
{
    public function index(Request $request)
    {
        $visits = SalesVisit::with(['customer', 'sales'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = strtoupper(trim($request->search));
                $q->where(function ($sub) use ($s) {
                    $sub->whereHas('customer', function ($cq) use ($s) {
                        $cq->whereRaw('UPPER(CAST(NM_CUST AS VARCHAR(100))) LIKE ?', ["%{$s}%"])
                           ->orWhereRaw('UPPER(CAST(KD_CUST AS VARCHAR(100))) LIKE ?', ["%{$s}%"]);
                    })->orWhereHas('sales', function ($sq) use ($s) {
                        $sq->whereRaw('UPPER(CAST(NM_PEG AS VARCHAR(100))) LIKE ?', ["%{$s}%"])
                           ->orWhereRaw('UPPER(CAST(KD_PEG AS VARCHAR(100))) LIKE ?', ["%{$s}%"]);
                    });
                });
            })
            ->orderBy('TANGGAL', 'desc')
            ->orderBy('NOMOR', 'desc')
            ->paginate(12)
            ->withQueryString();

        $visits->getCollection()->transform(function ($visit) {
            $visit->time_label = $visit->TANGGAL ? Carbon::parse($visit->TANGGAL)->format('H:i') : '-';
            return $visit;
        });

        return view('admin.kunjungan.index', compact('visits'));
    }

    public function show($id)
    {
        $visit = SalesVisit::with(['customer', 'sales', 'order.items.product'])
            ->where('NOMOR', $id)
            ->firstOrFail();

        return view('admin.kunjungan.show', compact('visit'));
    }
}
