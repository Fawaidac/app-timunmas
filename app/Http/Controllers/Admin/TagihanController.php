<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagihanController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $todayCarbon = Carbon::today();

        $totalPiutang = (float) (DB::table('VW_PIUTANG')
            ->where('SISA_PIUTANG', '>', 0.005)
            ->sum('SISA_PIUTANG') ?? 0);

        $jatuhTempoHariIni = (float) (DB::table('VW_PIUTANG')
            ->where('SISA_PIUTANG', '>', 0.005)
            ->whereNotNull('TGL_JATUH_TEMPO')
            ->whereRaw('CAST(TGL_JATUH_TEMPO AS DATE) = ?', [$today])
            ->sum('SISA_PIUTANG') ?? 0);

        $lewatJatuhTempo = (float) (DB::table('VW_PIUTANG')
            ->where('SISA_PIUTANG', '>', 0.005)
            ->whereNotNull('TGL_JATUH_TEMPO')
            ->whereRaw('CAST(TGL_JATUH_TEMPO AS DATE) < ?', [$today])
            ->sum('SISA_PIUTANG') ?? 0);

        $tertagihBulanIni = (float) (Payment::where('STATUS', 'approved')
            ->whereRaw('EXTRACT(MONTH FROM TGL_APPROVE) = ? AND EXTRACT(YEAR FROM TGL_APPROVE) = ?', [
                Carbon::now()->month, Carbon::now()->year,
            ])
            ->sum('JUMLAH') ?? 0);

        $unpaidCount = (int) DB::table('VW_PIUTANG')
            ->where('SISA_PIUTANG', '>', 0.005)
            ->where(function ($q) use ($today) {
                $q->whereNull('TGL_JATUH_TEMPO')
                    ->orWhereRaw('CAST(TGL_JATUH_TEMPO AS DATE) >= ?', [$today]);
            })->count();

        $overdueCount = (int) DB::table('VW_PIUTANG')
            ->where('SISA_PIUTANG', '>', 0.005)
            ->whereNotNull('TGL_JATUH_TEMPO')
            ->whereRaw('CAST(TGL_JATUH_TEMPO AS DATE) < ?', [$today])
            ->count();

        $paidCount = (int) DB::table('VW_PIUTANG')
            ->where('SISA_PIUTANG', '<=', 0.005)
            ->count();

        $invoiceByStatus = [
            'unpaid'         => $unpaidCount,
            'partially_paid' => 0,
            'paid'           => $paidCount,
            'overdue'        => $overdueCount,
        ];

        $query = Invoice::with('customer')
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = strtoupper($request->search);
                $q->where(function ($sq) use ($s) {
                    $sq->whereRaw('UPPER(CAST(NO_ENT AS VARCHAR(100))) LIKE ?', ["%{$s}%"])
                        ->orWhereRaw('UPPER(CAST(KD_CUST AS VARCHAR(100))) LIKE ?', ["%{$s}%"]);
                });
            })
            ->when($request->status === 'unpaid', function ($q) use ($today) {
                $q->where('SISA_PIUTANG', '>', 0.005)
                    ->where(function ($sq) use ($today) {
                        $sq->whereNull('TGL_JATUH_TEMPO')
                            ->orWhereRaw('CAST(TGL_JATUH_TEMPO AS DATE) >= ?', [$today]);
                    });
            })
            ->when($request->status === 'overdue', function ($q) use ($today) {
                $q->where('SISA_PIUTANG', '>', 0.005)
                    ->whereNotNull('TGL_JATUH_TEMPO')
                    ->whereRaw('CAST(TGL_JATUH_TEMPO AS DATE) < ?', [$today]);
            })
            ->when($request->status === 'paid', function ($q) {
                $q->where('SISA_PIUTANG', '<=', 0.005);
            })
            ->orderBy('TGL_JATUH_TEMPO', 'asc');

        $invoices = $query->paginate(15)->withQueryString();

        $invoices->getCollection()->transform(function ($invoice) use ($todayCarbon) {
            $dueDate = $invoice->TGL_JATUH_TEMPO ? Carbon::parse($invoice->TGL_JATUH_TEMPO) : null;
            $invoiceDate = $invoice->TANGGAL ? Carbon::parse($invoice->TANGGAL) : $todayCarbon;
            $umur = $invoiceDate->diffInDays($todayCarbon);

            if ($invoice->status === 'paid') {
                $invoice->badge_status = 'success';
                $invoice->badge_label = 'Lunas';
            } elseif ($dueDate && $dueDate->lt($todayCarbon)) {
                $invoice->badge_status = 'danger';
                $invoice->badge_label = 'Terlambat';
            } elseif ($dueDate && $dueDate->isSameDay($todayCarbon)) {
                $invoice->badge_status = 'warning';
                $invoice->badge_label = 'Jatuh tempo hari ini';
            } else {
                $invoice->badge_status = 'info';
                $invoice->badge_label = 'Kredit';
            }

            $invoice->umur_hari = $umur;

            return $invoice;
        });

        return view('admin.tagihan.index', compact(
            'invoices',
            'totalPiutang',
            'jatuhTempoHariIni',
            'lewatJatuhTempo',
            'tertagihBulanIni',
            'invoiceByStatus'
        ));
    }
}

