<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Tagihan sales = piutang dari VW_PIUTANG (sumber resmi, keputusan poin #1).
 * Filter per sales: kolom KD_PEG.
 */
class TagihanController extends Controller
{
    public function index(Request $request)
    {
        $kdPeg = auth()->user()->KD_PEG;
        $today = Carbon::today()->toDateString();

        // Hitung statistik via query DB (bukan collection) agar efisien
        $baseQuery = Invoice::when($kdPeg, fn ($q) => $q->where('KD_PEG', $kdPeg), fn ($q) => $q->whereRaw('1=0'))
            ->where('SISA_PIUTANG', '>', 0.005);

        $totalPiutang    = (float) (clone $baseQuery)->sum('SISA_PIUTANG');
        $jatuhTempoHariIni = (float) (clone $baseQuery)
            ->whereNotNull('TGL_JATUH_TEMPO')
            ->whereRaw('CAST(TGL_JATUH_TEMPO AS DATE) = ?', [$today])
            ->sum('SISA_PIUTANG');
        $lewatJatuhTempo = (float) (clone $baseQuery)
            ->whereNotNull('TGL_JATUH_TEMPO')
            ->whereRaw('CAST(TGL_JATUH_TEMPO AS DATE) < ?', [$today])
            ->sum('SISA_PIUTANG');

        // Tertagih bulan ini = payment approved bulan ini
        $tertagihBulanIni = (float) Payment::query()
            ->where('STATUS', 'approved')
            ->when($kdPeg, fn ($q) => $q->where('KD_PEG', $kdPeg))
            ->whereRaw('EXTRACT(MONTH FROM TGL_APPROVE) = ? AND EXTRACT(YEAR FROM TGL_APPROVE) = ?', [
                Carbon::now()->month, Carbon::now()->year,
            ])
            ->sum('JUMLAH');

        // Paginate invoice list
        $invoices = Invoice::with('customer')
            ->when($kdPeg, fn ($q) => $q->where('KD_PEG', $kdPeg), fn ($q) => $q->whereRaw('1=0'))
            ->where('SISA_PIUTANG', '>', 0.005)
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = strtoupper($request->search);
                $q->whereRaw('UPPER(NO_ENT) LIKE ?', ["%{$s}%"])
                  ->orWhereRaw('UPPER(KD_CUST) LIKE ?', ["%{$s}%"]);
            })
            ->orderBy('TGL_JATUH_TEMPO', 'asc')
            ->paginate(15)
            ->withQueryString();

        $todayCarbon = Carbon::today();
        $invoices->getCollection()->transform(function ($invoice) use ($todayCarbon) {
            $dueDate     = $invoice->TGL_JATUH_TEMPO ? Carbon::parse($invoice->TGL_JATUH_TEMPO) : null;
            $invoiceDate = $invoice->TANGGAL ? Carbon::parse($invoice->TANGGAL) : $todayCarbon;
            $invoice->umur_hari = $invoiceDate->diffInDays($todayCarbon);

            if ($dueDate === null) {
                $invoice->badge_status = 'secondary';
                $invoice->badge_label  = 'Tanpa tempo';
            } elseif ($dueDate->lt($todayCarbon)) {
                $invoice->badge_status = 'danger';
                $invoice->badge_label  = 'Terlambat';
            } elseif ($dueDate->isSameDay($todayCarbon)) {
                $invoice->badge_status = 'warning';
                $invoice->badge_label  = 'Jatuh tempo';
            } else {
                $invoice->badge_status = 'success';
                $invoice->badge_label  = 'Kredit';
            }

            return $invoice;
        });

        return view('sales.tagihan.index', compact(
            'invoices',
            'totalPiutang',
            'jatuhTempoHariIni',
            'lewatJatuhTempo',
            'tertagihBulanIni'
        ));
    }
}


