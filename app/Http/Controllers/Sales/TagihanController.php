<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Helpers\SalesHelper;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SalesOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagihanController extends Controller
{
    public function index(Request $request)
    {
        $kdPeg  = SalesHelper::kdPeg() ?: (auth()->user()->KD_PEG ?? null);
        $kdUser = auth()->user()->NM_USER ?? null;
        $today  = Carbon::today();
        $todayStr = $today->toDateString();
        $search = $request->filled('search') ? strtoupper(trim($request->search)) : null;
        $tab    = $request->query('tab', 'all'); 

        $scopedOrder = function ($q) use ($kdPeg, $kdUser) {
            $q->where('ST_JADI', 'OS');
            if ($kdPeg) {
                $q->where('KD_PEG', $kdPeg);
            } elseif ($kdUser) {
                $q->where('KD_USER', $kdUser);
            }
        };

        $scopedInvoice = function ($q) use ($kdPeg) {
            $q->where('SISA_PIUTANG', '>', 0.005);
            if ($kdPeg) {
                $q->where('KD_PEG', $kdPeg);
            }
        };
        
        $orderCountQuery = DB::table('MST_ORD_JUAL');
        $scopedOrder($orderCountQuery);
        $totalOrderCount = (int) $orderCountQuery->count();
        $totalOrderNominal = (float) ($orderCountQuery->sum('TOTAL') ?? 0);

        $invoiceCountQuery = DB::table('VW_PIUTANG');
        $scopedInvoice($invoiceCountQuery);
        $totalInvoiceCount = (int) $invoiceCountQuery->count();
        $totalInvoiceNominal = (float) ($invoiceCountQuery->sum('SISA_PIUTANG') ?? 0);

        $totalPiutang = $totalInvoiceNominal + $totalOrderNominal;

        $jatuhTempoHariIni = (float) (DB::table('VW_PIUTANG')
            ->tap($scopedInvoice)
            ->whereNotNull('TGL_JATUH_TEMPO')
            ->whereRaw('CAST(TGL_JATUH_TEMPO AS DATE) = ?', [$todayStr])
            ->sum('SISA_PIUTANG') ?? 0);

        $lewatJatuhTempo = (float) (DB::table('VW_PIUTANG')
            ->tap($scopedInvoice)
            ->whereNotNull('TGL_JATUH_TEMPO')
            ->whereRaw('CAST(TGL_JATUH_TEMPO AS DATE) < ?', [$todayStr])
            ->sum('SISA_PIUTANG') ?? 0);

        $overdueInvoiceCount = (int) DB::table('VW_PIUTANG')
            ->tap($scopedInvoice)
            ->whereNotNull('TGL_JATUH_TEMPO')
            ->whereRaw('CAST(TGL_JATUH_TEMPO AS DATE) <= ?', [$todayStr])
            ->count();

        $payQuery = Payment::query()->where('STATUS', 'approved');
        if ($kdPeg) {
            $payQuery->where('KD_PEG', $kdPeg);
        }
        $tertagihBulanIni = (float) ($payQuery
            ->whereRaw('EXTRACT(MONTH FROM TGL_APPROVE) = ? AND EXTRACT(YEAR FROM TGL_APPROVE) = ?', [
                Carbon::now()->month, Carbon::now()->year,
            ])
            ->sum('JUMLAH') ?? 0);

        $counts = [
            'all'     => $totalOrderCount + $totalInvoiceCount,
            'order'   => $totalOrderCount,
            'invoice' => $totalInvoiceCount,
            'overdue' => $overdueInvoiceCount,
        ];

        if ($tab === 'order') {
            $query = SalesOrder::with(['customer', 'payments'])
                ->tap($scopedOrder)
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sq) use ($search) {
                        $sq->whereRaw('UPPER(CAST(NO_ENT AS VARCHAR(100))) LIKE ?', ["%{$search}%"])
                           ->orWhereRaw('UPPER(CAST(KD_CUST AS VARCHAR(100))) LIKE ?', ["%{$search}%"]);
                    });
                })
                ->orderBy('TANGGAL', 'desc');

            $items = $query->paginate(15)->withQueryString();
            $items->getCollection()->transform(fn($order) => $this->transformOrder($order, $today));

        } elseif ($tab === 'invoice') {
            $query = Invoice::with(['customer', 'payments'])
                ->tap($scopedInvoice)
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sq) use ($search) {
                        $sq->whereRaw('UPPER(CAST(NO_ENT AS VARCHAR(100))) LIKE ?', ["%{$search}%"])
                           ->orWhereRaw('UPPER(CAST(KD_CUST AS VARCHAR(100))) LIKE ?', ["%{$search}%"]);
                    });
                })
                ->orderBy('TGL_JATUH_TEMPO', 'asc');

            $items = $query->paginate(15)->withQueryString();
            $items->getCollection()->transform(fn($invoice) => $this->transformInvoice($invoice, $today));

        } elseif ($tab === 'overdue') {
            $query = Invoice::with(['customer', 'payments'])
                ->tap($scopedInvoice)
                ->whereNotNull('TGL_JATUH_TEMPO')
                ->whereRaw('CAST(TGL_JATUH_TEMPO AS DATE) <= ?', [$todayStr])
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sq) use ($search) {
                        $sq->whereRaw('UPPER(CAST(NO_ENT AS VARCHAR(100))) LIKE ?', ["%{$search}%"])
                           ->orWhereRaw('UPPER(CAST(KD_CUST AS VARCHAR(100))) LIKE ?', ["%{$search}%"]);
                    });
                })
                ->orderBy('TGL_JATUH_TEMPO', 'asc');

            $items = $query->paginate(15)->withQueryString();
            $items->getCollection()->transform(fn($invoice) => $this->transformInvoice($invoice, $today));

        } else {
            $orderList = SalesOrder::with(['customer', 'payments'])
                ->tap($scopedOrder)
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sq) use ($search) {
                        $sq->whereRaw('UPPER(CAST(NO_ENT AS VARCHAR(100))) LIKE ?', ["%{$search}%"])
                           ->orWhereRaw('UPPER(CAST(KD_CUST AS VARCHAR(100))) LIKE ?', ["%{$search}%"]);
                    });
                })
                ->orderBy('TANGGAL', 'desc')
                ->get()
                ->map(fn($order) => $this->transformOrder($order, $today));

            $invoiceQuery = Invoice::with(['customer', 'payments'])
                ->tap($scopedInvoice)
                ->when($search, function ($q) use ($search) {
                    $q->where(function ($sq) use ($search) {
                        $sq->whereRaw('UPPER(CAST(NO_ENT AS VARCHAR(100))) LIKE ?', ["%{$search}%"])
                           ->orWhereRaw('UPPER(CAST(KD_CUST AS VARCHAR(100))) LIKE ?', ["%{$search}%"]);
                    });
                })
                ->orderBy('TGL_JATUH_TEMPO', 'asc');

            $items = $invoiceQuery->paginate(15)->withQueryString();
            $items->getCollection()->transform(fn($invoice) => $this->transformInvoice($invoice, $today));

            if ($items->currentPage() === 1 && $orderList->isNotEmpty()) {
                $merged = $orderList->concat($items->getCollection());
                $items->setCollection($merged);
            }
        }

        return view('sales.tagihan.index', compact(
            'items',
            'tab',
            'counts',
            'totalPiutang',
            'totalOrderNominal',
            'totalInvoiceNominal',
            'jatuhTempoHariIni',
            'lewatJatuhTempo',
            'tertagihBulanIni'
        ));
    }

    private function transformOrder($order, Carbon $today)
    {
        $orderDate = $order->TANGGAL ? Carbon::parse($order->TANGGAL) : $today;
        $topDays   = (int) ($order->TOP ?: 7);
        $dueDate   = (clone $orderDate)->addDays($topDays);
        $umur      = $orderDate->diffInDays($today);

        $approvedPaid = (float) $order->payments->where('STATUS', 'approved')->sum('JUMLAH');
        $totalNominal = (float) ($order->TOTAL ?? 0);
        $sisa         = max(0, $totalNominal - $approvedPaid);

        $order->_type             = 'order';
        $order->_type_label       = 'Sales Order';
        $order->_id               = $order->NO_ENT;
        $order->_nomor            = $order->NO_ENT;
        $order->_tanggal          = $orderDate;
        $order->_nama_customer    = $order->customer?->NM_CUST ?? $order->customer?->name ?? $order->KD_CUST ?? '-';
        $order->_kode_customer    = $order->KD_CUST;
        $order->_due_date         = $dueDate;
        $order->_total            = $totalNominal;
        $order->_sisa             = $sisa;
        $order->_umur_hari        = $umur;
        $order->_pending_payment  = $order->payments->firstWhere('STATUS', 'pending_approval');
        $order->_rejected_payment = $order->payments->firstWhere('STATUS', 'rejected');
        $order->_latest_payment   = $order->payments->sortByDesc('NOMOR')->first();

        if ($sisa <= 0.005) {
            $order->_badge_status = 'success';
            $order->_badge_label  = 'Lunas';
            $order->_is_overdue   = false;
        } elseif ($dueDate->lt($today)) {
            $order->_badge_status = 'danger';
            $order->_badge_label  = 'Terlambat';
            $order->_is_overdue   = true;
        } elseif ($dueDate->isSameDay($today)) {
            $order->_badge_status = 'warning';
            $order->_badge_label  = 'Jatuh Tempo Hari Ini';
            $order->_is_overdue   = true;
        } else {
            $order->_badge_status = 'info';
            $order->_badge_label  = 'Tempo ' . $topDays . ' Hari';
            $order->_is_overdue   = false;
        }

        return $order;
    }

    private function transformInvoice($invoice, Carbon $today)
    {
        $dueDate     = $invoice->TGL_JATUH_TEMPO ? Carbon::parse($invoice->TGL_JATUH_TEMPO) : null;
        $invoiceDate = $invoice->TANGGAL ? Carbon::parse($invoice->TANGGAL) : $today;
        $umur        = $invoiceDate->diffInDays($today);

        $invoice->_type             = 'invoice';
        $invoice->_type_label       = 'Faktur Penjualan';
        $invoice->_id               = $invoice->NO_ENT;
        $invoice->_nomor            = $invoice->NO_ENT;
        $invoice->_tanggal          = $invoiceDate;
        $invoice->_nama_customer    = $invoice->customer?->NM_CUST ?? $invoice->customer?->name ?? $invoice->KD_CUST ?? '-';
        $invoice->_kode_customer    = $invoice->KD_CUST;
        $invoice->_due_date         = $dueDate;
        $invoice->_total            = (float) ($invoice->NETTO ?? 0);
        $invoice->_sisa             = (float) ($invoice->SISA_PIUTANG ?? 0);
        $invoice->_umur_hari        = $umur;
        $invoice->_pending_payment  = $invoice->payments->firstWhere('STATUS', 'pending_approval');
        $invoice->_rejected_payment = $invoice->payments->firstWhere('STATUS', 'rejected');
        $invoice->_latest_payment   = $invoice->latestPayment;

        if ($invoice->_sisa <= 0.005) {
            $invoice->_badge_status = 'success';
            $invoice->_badge_label  = 'Lunas';
            $invoice->_is_overdue   = false;
        } elseif ($dueDate === null) {
            $invoice->_badge_status = 'secondary';
            $invoice->_badge_label  = 'Tanpa Tempo';
            $invoice->_is_overdue   = false;
        } elseif ($dueDate->lt($today)) {
            $invoice->_badge_status = 'danger';
            $invoice->_badge_label  = 'Terlambat';
            $invoice->_is_overdue   = true;
        } elseif ($dueDate->isSameDay($today)) {
            $invoice->_badge_status = 'warning';
            $invoice->_badge_label  = 'Jatuh Tempo Hari Ini';
            $invoice->_is_overdue   = true;
        } else {
            $invoice->_badge_status = 'success';
            $invoice->_badge_label  = 'Kredit';
            $invoice->_is_overdue   = false;
        }

        return $invoice;
    }
}
