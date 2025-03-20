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

        $vehicles = Vehicle::with('services')
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('plate_no', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%")
                        ->orWhere('owner_name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

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