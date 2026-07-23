<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesVisit;
use Carbon\Carbon;

class VisitController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // Get semua kunjungan hari ini dari semua sales
        $visits = SalesVisit::with(['sales', 'customer'])
            ->whereDate('visit_date', $today)
            ->orderBy('visit_date', 'asc')
            ->get()
            ->map(function($visit) {
                // Map status ke badge class
                $badgeMap = [
                    'scheduled' => ['class' => 'badge-warning', 'label' => 'Terjadwal'],
                    'in_progress' => ['class' => 'badge-orange', 'label' => 'Berlangsung'],
                    'completed' => ['class' => 'badge-success', 'label' => 'Selesai'],
                    'cancelled' => ['class' => 'badge-danger', 'label' => 'Dibatalkan'],
                ];
                
                $visit->badge_class = $badgeMap[$visit->status]['class'] ?? 'badge-secondary';
                $visit->badge_label = $badgeMap[$visit->status]['label'] ?? ucfirst($visit->status);
                $visit->time_label = Carbon::parse($visit->visit_date)->format('H:i');
                
                return $visit;
            });

        return view('admin.kunjungan.index', compact('visits'));
    }

    public function show($id)
    {
        $visit = SalesVisit::with(['customer', 'sales', 'order.items.product'])
            ->findOrFail($id);

        return view('admin.kunjungan.show', compact('visit'));
    }
}
