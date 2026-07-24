<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\StoreVisitRequest;
use App\Http\Requests\Sales\CheckinVisitRequest;
use App\Models\SalesVisit;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VisitController extends Controller
{
    public function index()
    {
        $visits = SalesVisit::with(['customer', 'sales'])
            ->where('sales_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('sales.kunjungan.index', compact('visits'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        return view('sales.kunjungan.create', compact('customers'));
    }

    public function store(StoreVisitRequest $request)
    {
        $visit = SalesVisit::create([
            'sales_id'    => auth()->id(),
            'customer_id' => $request->customer_id,
            'visit_date'  => $request->visit_date,
            'purpose'     => $request->purpose,
            'notes'       => $request->notes,
            'status'      => 'scheduled',
        ]);

        return redirect()->route('sales.kunjungan.index')
            ->with('success', 'Kunjungan berhasil dijadwalkan.');
    }

    public function show($id)
    {
        $visit = SalesVisit::with(['customer', 'sales', 'order.items.product'])
            ->where('sales_id', auth()->id())
            ->findOrFail($id);

        return view('sales.kunjungan.show', compact('visit'));
    }

    public function edit($id)
    {
        $visit = SalesVisit::with('customer')
            ->where('sales_id', auth()->id())
            ->findOrFail($id);

        return view('sales.kunjungan.edit', compact('visit'));
    }

    public function update(Request $request, $id)
    {
        $visit = SalesVisit::where('sales_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'purpose' => 'required|in:merchandising,collection,order',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'notes' => 'nullable|string|max:500'
        ]);

        $visit->update($validated);

        return redirect()->route('sales.kunjungan.show', $visit->id)
            ->with('success', 'Kunjungan berhasil diupdate.');
    }

    public function destroy($id)
    {
        $visit = SalesVisit::where('sales_id', auth()->id())->findOrFail($id);
        
        // Check if visit has order
        if ($visit->order) {
            return redirect()->route('sales.kunjungan.show', $visit->id)
                ->with('error', 'Tidak dapat menghapus kunjungan yang sudah memiliki order.');
        }

        $visit->delete();

        return redirect()->route('sales.kunjungan.index')
            ->with('success', 'Kunjungan berhasil dihapus.');
    }

    public function checkin($id)
    {
        $visit = SalesVisit::with('customer')
            ->where('sales_id', auth()->id())
            ->where('status', 'scheduled')
            ->findOrFail($id);

        return view('sales.checkin.index', compact('visit'));
    }

    public function storeCheckin(CheckinVisitRequest $request, $id)
    {
        $visit = SalesVisit::where('sales_id', auth()->id())
            ->where('status', 'scheduled')
            ->findOrFail($id);

        $customer = $visit->customer;

        // Hitung jarak dari koordinat customer (jika ada)
        $distance = null;
        if ($customer->latitude && $customer->longitude) {
            $distance = $this->calculateDistance(
                $request->checkin_latitude,
                $request->checkin_longitude,
                $customer->latitude,
                $customer->longitude
            );
        }

        $visit->update([
            'checkin_time'      => now(),
            'checkin_latitude'  => $request->checkin_latitude,
            'checkin_longitude' => $request->checkin_longitude,
            'distance_meters'   => $distance,
            'status'            => 'in_progress',
        ]);

        return redirect()->route('sales.kunjungan.show', $visit->id)
            ->with('success', 'Check-in berhasil. Jarak dari customer: ' . ($distance ? round($distance) . ' meter' : 'tidak diketahui'));
    }

    public function createOrder($id)
    {
        $visit = SalesVisit::with('customer', 'order')
            ->where('sales_id', auth()->id())
            ->where('status', 'in_progress')
            ->findOrFail($id);

        // Cek apakah visit sudah punya order
        if ($visit->order) {
            return redirect()->route('sales.order.show', $visit->order->id)
                ->with('success', 'Kunjungan ini sudah memiliki sales order.');
        }

        $products = \App\Models\Product::with('warehouses')->orderBy('name')->get();

        return view('sales.order.create', compact('visit', 'products'));
    }

    /**
     * Hitung jarak antar 2 koordinat GPS (Haversine formula)
     * Return dalam meter
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
