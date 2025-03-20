<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\Vehicle;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $startDate = $request->input('startDate');
        $endDate = $request->input('endDate');

        $services = Service::with(['vehicle', 'items'])
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('vehicle', function ($query) use ($search) {
                        $query->where('plate_no', 'like', "%{$search}%")
                            ->orWhere('brand', 'like', "%{$search}%")
                            ->orWhere('model', 'like', "%{$search}%")
                            ->orWhere('owner_name', 'like', "%{$search}%");
                    });
                });
            })
            ->when($startDate, function ($query, $startDate) {
                $query->whereDate('service_date', '>=', $startDate);
            })
            ->when($endDate, function ($query, $endDate) {
                $query->whereDate('service_date', '<=', $endDate);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Services/Index', [
            'services' => $services,
            'filters' => [
                'search' => $search,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]
        ]);
    }

    public function create(Vehicle $vehicle)
    {
        $categories = Category::all();
        return Inertia::render('Services/Create', [
            'vehicle' => $vehicle,
            'categories' => $categories
        ]);
    }

    public function store(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'mileage' => 'required|integer',
            'service_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.category_id' => 'required|exists:categories,id',
            'items.*.completed' => 'required|boolean',
            'items.*.notes' => 'nullable|string'
        ]);

        $service = $vehicle->services()->create([
            'mileage' => $validated['mileage'],
            'service_date' => $validated['service_date']
        ]);

        foreach ($validated['items'] as $item) {
            $service->items()->create([
                'category_id' => $item['category_id'],
                'completed' => $item['completed'],
                'notes' => $item['notes'] ?? null
            ]);
        }

        return redirect()->route('services.show', $service)
            ->with('message', 'Servis kaydı başarıyla oluşturuldu.');
    }

    public function show(Service $service)
    {
        $service->load(['vehicle', 'items.category.parent']);
        return Inertia::render('Services/Show', [
            'service' => $service
        ]);
    }

    public function edit(Service $service)
    {
        $service->load(['vehicle', 'items.category.parent']);
        $categories = Category::all();
        
        return Inertia::render('Services/Edit', [
            'service' => $service,
            'categories' => $categories
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'mileage' => 'required|integer',
            'service_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.category_id' => 'required|exists:categories,id',
            'items.*.completed' => 'required|boolean',
            'items.*.notes' => 'nullable|string'
        ]);

        $service->update([
            'mileage' => $validated['mileage'],
            'service_date' => $validated['service_date']
        ]);

        // Delete existing items and create new ones
        $service->items()->delete();
        
        foreach ($validated['items'] as $item) {
            $service->items()->create([
                'category_id' => $item['category_id'],
                'completed' => $item['completed'],
                'notes' => $item['notes'] ?? null
            ]);
        }

        return redirect()->route('services.show', $service)
            ->with('message', 'Servis kaydı başarıyla güncellendi.');
    }

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('vehicles.show', $service->vehicle_id)
            ->with('message', 'Servis kaydı başarıyla silindi.');
    }
}