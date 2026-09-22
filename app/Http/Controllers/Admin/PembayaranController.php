<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Pegawai;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $pendingApproval = Payment::where('STATUS', 'pending_approval')->count();
        $approved = Payment::where('STATUS', 'approved')->count();
        $rejected = Payment::where('STATUS', 'rejected')->count();

        $totalPending = (float) (Payment::where('STATUS', 'pending_approval')->sum('JUMLAH') ?? 0);
        $totalApproved = (float) (Payment::where('STATUS', 'approved')->sum('JUMLAH') ?? 0);

        $approvedBulanIni = (float) (Payment::where('STATUS', 'approved')
            ->whereRaw('EXTRACT(MONTH FROM TGL_APPROVE) = ? AND EXTRACT(YEAR FROM TGL_APPROVE) = ?', [
                Carbon::now()->month, Carbon::now()->year,
            ])
            ->sum('JUMLAH') ?? 0);

        $status = $request->query('status', 'pending');
        $query = Payment::with(['customer', 'invoice', 'sales', 'approver']);

        if ($status === 'approved') {
            $query->where('STATUS', 'approved');
        } elseif ($status === 'rejected') {
            $query->where('STATUS', 'rejected');
        } elseif ($status === 'all') {
            // all
        } else {
            $status = 'pending';
            $query->where('STATUS', 'pending_approval');
        }

        if ($request->filled('search')) {
            $s = strtoupper($request->search);
            $query->where(function ($q) use ($s) {
                $q->whereRaw('UPPER(CAST(NO_BUKTI AS VARCHAR(100))) LIKE ?', ["%{$s}%"])
                    ->orWhereRaw('UPPER(CAST(NO_ENT AS VARCHAR(100))) LIKE ?', ["%{$s}%"])
                    ->orWhereRaw('UPPER(CAST(KD_CUST AS VARCHAR(100))) LIKE ?', ["%{$s}%"]);
            });
        }

        $payments = $query->orderBy('DIBUAT', 'desc')->paginate(10)->withQueryString();

        return view('admin.pembayaran.index', compact(
            'payments',
            'status',
            'pendingApproval',
            'approved',
            'rejected',
            'totalPending',
            'totalApproved',
            'approvedBulanIni'
        ));
    }

    public function show($id)
    {
        $payment = Payment::with(['customer', 'invoice', 'approver'])
            ->where('NOMOR', $id)->firstOrFail();

        return view('admin.pembayaran.show', compact('payment'));
    }

    public function approve(Request $request, $id)
    {
        $payment = Payment::where('NOMOR', $id)->firstOrFail();

        if ($payment->STATUS !== 'pending_approval') {
            return redirect()->back()->with('error', 'Pembayaran ini sudah diproses.');
        }

        DB::transaction(function () use ($payment) {
            $faktur = DB::table('MST_JUAL')->where('NO_ENT', $payment->NO_ENT)->first();

            if ($faktur) {
                $jumlah = (float) $payment->JUMLAH;

                DB::table('MST_JUAL')->where('NO_ENT', $payment->NO_ENT)->update([
                    'JML_BAYAR' => (float) $faktur->JML_BAYAR + $jumlah,
                ]);

                $sisaBaru = (float) $faktur->NETTO
                    - ((float) $faktur->JML_BAYAR + $jumlah + (float) $faktur->U_MUKA
                        + (float) $faktur->JML_RETUR + (float) $faktur->JML_KUPON);

                $noKartu = (int) (DB::select('SELECT GEN_ID(DET_KRT_PIUTANG_NOMOR_GEN, 1) AS ID FROM RDB$DATABASE')[0]->ID ?? 0);
                $nmPeg = Pegawai::find($payment->KD_PEG)->NM_PEG ?? null;

                DB::table('DET_KRT_PIUTANG')->insert([
                    'NOMOR'      => $noKartu,
                    'TANGGAL'    => now()->format('Y-m-d H:i:s'),
                    'NO_BUKTI'   => $payment->NO_BUKTI,
                    'KD_CUST'    => $payment->KD_CUST,
                    'KET'        => 'PEMBAYARAN',
                    'DEBET'      => 0,
                    'KREDIT'     => $jumlah,
                    'SALDO'      => max(0, $sisaBaru),
                    'BYR_TUNAI'  => $payment->METODE === 'cash' ? $jumlah : 0,
                    'BYR_CEK'    => $payment->METODE === 'giro' ? $jumlah : 0,
                    'TGL_JT'     => null,
                    'STS_SIMPAN' => 0,
                    'PENJUALAN'  => 0,
                    'PEMBAYARAN' => $jumlah,
                    'NM_PEG'     => $nmPeg,
                ]);
            }

            $payment->update([
                'STATUS'          => 'approved',
                'NO_USER_APPROVE' => auth()->user()->NO_USER,
                'TGL_APPROVE'     => now()->format('Y-m-d H:i:s'),
            ]);

            \App\Services\NotificationService::send([
                'type'        => 'payment_approved',
                'target_role' => 'sales',
                'target_user' => $payment->KD_PEG,
                'title'       => 'Pembayaran Disetujui',
                'message'     => 'Titip pembayaran ' . $payment->NO_BUKTI . ' (Rp ' . number_format($payment->JUMLAH, 0, ',', '.') . ') telah disetujui Admin.',
                'url'         => route('sales.tagihan.index'),
                'icon'        => '✅',
            ]);
        });

        return redirect()->route('admin.pembayaran.show', $id)
            ->with('success', 'Pembayaran berhasil diapprove dan piutang legacy telah di-update.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $payment = Payment::where('NOMOR', $id)->firstOrFail();

        if ($payment->STATUS !== 'pending_approval') {
            return redirect()->back()->with('error', 'Pembayaran ini sudah diproses.');
        }

        $payment->update([
            'STATUS'          => 'rejected',
            'ALASAN'          => $request->rejection_reason,
            'NO_USER_APPROVE' => auth()->user()->NO_USER,
            'TGL_APPROVE'     => now()->format('Y-m-d H:i:s'),
        ]);

        \App\Services\NotificationService::send([
            'type'        => 'payment_rejected',
            'target_role' => 'sales',
            'target_user' => $payment->KD_PEG,
            'title'       => 'Pembayaran Ditolak',
            'message'     => 'Titip pembayaran ' . $payment->NO_BUKTI . ' ditolak Admin. Alasan: ' . $request->rejection_reason,
            'url'         => route('sales.pembayaran.index', $payment->NO_ENT),
            'icon'        => '❌',
        ]);

        return redirect()->route('admin.pembayaran.show', $id)
            ->with('success', 'Pembayaran berhasil ditolak.');
    }
}
