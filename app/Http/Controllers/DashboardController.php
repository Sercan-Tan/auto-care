<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $totalVehicles = Vehicle::count();
        
        $vehicleStats = [
            'monthlyServices' => Service::whereMonth('service_date', now()->month)
                ->whereYear('service_date', now()->year)
                ->count(),
            'pendingTasks' => Service::whereHas('items', function($query) {
                $query->where('completed', false);
            })->count()
        ];

        $recentServices = Service::with(['vehicle', 'items'])
            ->latest('service_date')
            ->take(5)
            ->get();

        $pendingServices = Service::with(['vehicle', 'items'])
            ->whereHas('items', function($query) {
                $query->where('completed', false);
            })
            ->withCount(['items as pending_items_count' => function($query) {
                $query->where('completed', false);
            }])
            ->latest('service_date')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'totalVehicles' => $totalVehicles,
            'vehicleStats' => $vehicleStats,
            'recentServices' => $recentServices,
            'pendingServices' => $pendingServices,
        ]);
    }
}