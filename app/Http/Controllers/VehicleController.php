<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $vehicles = Vehicle::with(['services' => function($query) {
                $query->select('id', 'vehicle_id', 'labor_cost', 'service_date');
            }])
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('plate_no', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('owner_name', 'like', "%{$search}%");
                });
            })
            ->withCount([
                'services', 
                'services as total_labor_cost_sum' => function($query) {
                    $query->select(\DB::raw('sum(labor_cost)'));
                },
                'services as total_parts_cost_sum' => function($query) {
                    $query->join('service_items', 'services.id', '=', 'service_items.service_id')
                          ->select(\DB::raw('sum(service_items.part_cost)'));
                }
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Hesapla toplam maliyeti (işçilik + parça)
        $vehicles->getCollection()->transform(function ($vehicle) {
            $vehicle->total_cost = $vehicle->total_labor_cost_sum + $vehicle->total_parts_cost_sum;
            return $vehicle;
        });

        return Inertia::render('Vehicles/Index', [
            'vehicles' => $vehicles,
            'filters' => ['search' => $search]
        ]);
    }

    public function create()
    {
        return Inertia::render('Vehicles/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate_no' => 'required|unique:vehicles',
            'brand' => 'required',
            'model' => 'required',
            'year' => 'required|integer',
            'chassis_no' => 'required|unique:vehicles',
            'owner_name' => 'required',
            'owner_phone' => 'required',
            'owner_email' => 'nullable|email'
        ]);

        Vehicle::create($validated);

        return redirect()->route('vehicles.index')
            ->with('message', 'Araç başarıyla oluşturuldu.');
    }

    public function show(Vehicle $vehicle)
    {
        // Kategori ilişkilerini parent ilişkisiyle birlikte yükle
        $vehicle->load(['services.items.category.parent']);
        
        // Toplam maliyet hesaplamaları
        $totalLaborCost = $vehicle->services->sum('labor_cost');
        $totalPartsCost = $vehicle->services->flatMap->items->sum('part_cost');
        $totalCost = $totalLaborCost + $totalPartsCost;
        
        // Maliyet bilgilerini ekle
        $vehicle->total_labor_cost = $totalLaborCost;
        $vehicle->total_parts_cost = $totalPartsCost;
        $vehicle->total_cost = $totalCost;
        
        return Inertia::render('Vehicles/Show', [
            'vehicle' => $vehicle
        ]);
    }

    public function edit(Vehicle $vehicle)
    {
        return Inertia::render('Vehicles/Edit', [
            'vehicle' => $vehicle
        ]);
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'plate_no' => 'required|unique:vehicles,plate_no,' . $vehicle->id,
            'brand' => 'required',
            'model' => 'required',
            'year' => 'required|integer',
            'chassis_no' => 'required|unique:vehicles,chassis_no,' . $vehicle->id,
            'owner_name' => 'required',
            'owner_phone' => 'required',
            'owner_email' => 'nullable|email'
        ]);

        $vehicle->update($validated);
        
        return redirect()->route('vehicles.show', $vehicle->id)
            ->with('message', 'Araç başarıyla güncellendi.');
    }

    public function destroy(Vehicle $vehicle)
    {
        // Önce aracın servislerini sil
        $vehicle->services()->delete();
        // Sonra aracı sil
        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('message', 'Araç başarıyla silindi.');
    }
}