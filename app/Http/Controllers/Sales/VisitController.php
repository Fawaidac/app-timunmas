<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Helpers\SalesHelper;
use App\Http\Requests\Sales\StoreVisitRequest;
use App\Http\Requests\Sales\CheckinVisitRequest;
use App\Models\SalesVisit;
use App\Models\Customer;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    private function salesKdPeg(): ?string
    {
        return SalesHelper::kdPeg();
    }

    private function scoped()
    {
        return SalesVisit::when(
            $this->salesKdPeg(),
            fn ($q, $p) => $q->where('KD_PEG', $p),
            fn ($q) => $q->whereRaw('1=0')
        );
    }

    public function index(Request $request)
    {
        $visits = $this->scoped()->with('customer')
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = strtoupper($request->search);
                $q->whereHas('customer', fn ($cq) => $cq->whereRaw('UPPER(NM_CUST) LIKE ?', ["%{$s}%"]));
            })
            ->orderBy('TANGGAL', 'desc')
            ->orderBy('NOMOR', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('sales.kunjungan.index', compact('visits'));
    }

    public function create()
    {
        $kdPeg = $this->salesKdPeg();
        $customers = Customer::when($kdPeg, fn($q, $p) => $q->where('KD_PEG', $p))
            ->orderBy('NM_CUST')
            ->get();

        return view('sales.kunjungan.create', compact('customers'));
    }

    public function store(StoreVisitRequest $request)
    {
        SalesVisit::create([
            'KD_PEG'  => $this->salesKdPeg(),
            'KD_CUST' => $request->customer_id,
            'TANGGAL' => $request->visit_date,
            'TUJUAN'  => $request->purpose,
            'CATATAN' => $request->notes,
            'STATUS'  => 'scheduled',
        ]);

        return redirect()->route('sales.kunjungan.index')
            ->with('success', 'Kunjungan berhasil dijadwalkan.');
    }

    public function show($id)
    {
        $visit = $this->scoped()->with(['customer', 'order.items.product'])
            ->where('NOMOR', $id)->firstOrFail();

        return view('sales.kunjungan.show', compact('visit'));
    }

    public function edit($id)
    {
        $visit = $this->scoped()->with('customer')
            ->where('NOMOR', $id)->firstOrFail();

        return view('sales.kunjungan.edit', compact('visit'));
    }

    public function update(Request $request, $id)
    {
        $visit = $this->scoped()->where('NOMOR', $id)->firstOrFail();

        $validated = $request->validate([
            'purpose' => 'required|in:merchandising,collection,order',
            'status'  => 'required|in:scheduled,in_progress,completed,cancelled',
            'notes'   => 'nullable|string|max:500',
        ]);

        $visit->update([
            'TUJUAN'  => $validated['purpose'],
            'STATUS'  => $validated['status'],
            'CATATAN' => $validated['notes'] ?? $visit->CATATAN,
        ]);

        return redirect()->route('sales.kunjungan.show', $visit->NOMOR)
            ->with('success', 'Kunjungan berhasil diupdate.');
    }

    public function destroy($id)
    {
        $visit = $this->scoped()->where('NOMOR', $id)->firstOrFail();

        if ($visit->order) {
            return redirect()->route('sales.kunjungan.show', $visit->NOMOR)
                ->with('error', 'Tidak dapat menghapus kunjungan yang sudah memiliki order.');
        }

        $visit->delete();

        return redirect()->route('sales.kunjungan.index')
            ->with('success', 'Kunjungan berhasil dihapus.');
    }

    public function checkin($id)
    {
        $visit = $this->scoped()->with('customer')
            ->where('NOMOR', $id)
            ->where('STATUS', 'scheduled')
            ->firstOrFail();

        return view('sales.checkin.index', compact('visit'));
    }

    public function storeCheckin(CheckinVisitRequest $request, $id)
    {
        $visit = $this->scoped()
            ->where('NOMOR', $id)
            ->where('STATUS', 'scheduled')
            ->firstOrFail();

        $userLat = (float) $request->checkin_latitude;
        $userLon = (float) $request->checkin_longitude;

        // Validasi server-side jarak GPS (anti-manipulasi JS)
        $customer = $visit->customer;
        if ($customer && $customer->latitude && $customer->longitude) {
            $jarakMeter = $this->haversineDistance(
                $userLat, $userLon,
                (float) $customer->latitude,
                (float) $customer->longitude
            );

            if ($jarakMeter > 150) {
                return redirect()->back()
                    ->withErrors(['checkin_latitude' => "Check-in gagal: Anda berada {$jarakMeter} meter dari lokasi customer. Maksimal 100 meter."])
                    ->withInput();
            }
        }

        $visit->update([
            'JAM_CHECKIN' => now()->format('Y-m-d H:i:s'),
            'LAT_CHECKIN' => round($userLat, 8),
            'LON_CHECKIN' => round($userLon, 8),
            'JARAK_M'     => isset($jarakMeter) ? round($jarakMeter) : null,
            'STATUS'      => 'in_progress',
        ]);

        return redirect()->route('sales.kunjungan.show', $visit->NOMOR)
            ->with('success', 'Check-in berhasil dicatat.');
    }

    public function createOrder($id)
    {
        $visit = $this->scoped()->with('customer', 'order')
            ->where('NOMOR', $id)
            ->where('STATUS', 'in_progress')
            ->firstOrFail();

        if ($visit->order) {
            return redirect()->route('sales.order.show', $visit->order->NO_ENT)
                ->with('success', 'Kunjungan ini sudah memiliki sales order.');
        }

        $products = \App\Models\Product::orderBy('NM_BRG')->get();

        return view('sales.order.create', compact('visit', 'products'));
    }

    /**
     * Hitung jarak dua titik GPS menggunakan formula Haversine (dalam meter).
     */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
