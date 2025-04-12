<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;

class CostController extends Controller
{
    public function index(Request $request)
    {
        // Define default filter period (current month)
        $filterPeriod = $request->query('period', 'current_month');
        $startDate = null;
        $endDate = null;
        
        // Set date range based on selected period
        switch ($filterPeriod) {
            case 'current_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'last_3_months':
                $startDate = Carbon::now()->subMonths(3)->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'last_6_months':
                $startDate = Carbon::now()->subMonths(6)->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
            case 'current_year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'custom':
                $startDate = $request->filled('start_date') 
                    ? Carbon::parse($request->query('start_date')) 
                    : Carbon::now()->subMonth();
                $endDate = $request->filled('end_date') 
                    ? Carbon::parse($request->query('end_date')) 
                    : Carbon::now();
                break;
        }
        
        // Get total costs
        $totals = $this->getTotals($startDate, $endDate);
        
        // Get monthly costs for the chart
        $monthlyCosts = $this->getMonthlyCosts($startDate, $endDate);
        
        // Get top services by cost
        $topServices = $this->getTopServices($startDate, $endDate);
        
        return Inertia::render('Costs/Index', [
            'totals' => $totals,
            'monthlyCosts' => $monthlyCosts,
            'topServices' => $topServices,
            'filters' => [
                'period' => $filterPeriod,
                'startDate' => $startDate ? $startDate->format('Y-m-d') : null,
                'endDate' => $endDate ? $endDate->format('Y-m-d') : null,
            ],
        ]);
    }
    
    private function getTotals($startDate, $endDate)
    {
        // Get total labor costs
        $laborCosts = Service::whereBetween('service_date', [$startDate, $endDate])
            ->sum('labor_cost');
        
        // Get total part costs
        $partCosts = ServiceItem::whereHas('service', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('service_date', [$startDate, $endDate]);
        })->sum('part_cost');
        
        return [
            'laborCosts' => $laborCosts,
            'partCosts' => $partCosts,
            'totalCosts' => $laborCosts + $partCosts
        ];
    }
    
    private function getMonthlyCosts($startDate, $endDate)
    {
        // Get costs grouped by month for the chart
        $laborCostsByMonth = Service::whereBetween('service_date', [$startDate, $endDate])
            ->selectRaw('SUM(labor_cost) as total, YEAR(service_date) as year, MONTH(service_date) as month')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
            
        $partCostsByMonth = DB::table('service_items')
            ->join('services', 'service_items.service_id', '=', 'services.id')
            ->whereBetween('services.service_date', [$startDate, $endDate])
            ->selectRaw('SUM(service_items.part_cost) as total, YEAR(services.service_date) as year, MONTH(services.service_date) as month')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();
        
        // Format the data for the chart
        $months = [];
        $startMonth = (clone $startDate)->startOfMonth();
        $endMonth = (clone $endDate)->startOfMonth();
        
        // Türkçe ay isimlerini kullanmak için locale'i ayarla
        Carbon::setLocale('tr');
        
        while ($startMonth->lte($endMonth)) {
            $year = $startMonth->year;
            $month = $startMonth->month;
            
            // Türkçe ay ve yıl formatı (örn: "Nis 2025")
            $monthLabel = $startMonth->translatedFormat('M Y');
            
            $laborTotal = $laborCostsByMonth
                ->where('year', $year)
                ->where('month', $month)
                ->first()?->total ?? 0;
                
            $partTotal = $partCostsByMonth
                ->where('year', $year)
                ->where('month', $month)
                ->first()?->total ?? 0;
            
            $months[] = [
                'month' => $monthLabel,
                'labor' => (float) $laborTotal,
                'parts' => (float) $partTotal,
                'total' => (float) $laborTotal + (float) $partTotal
            ];
            
            $startMonth->addMonth();
        }
        
        return $months;
    }
    
    private function getTopServices($startDate, $endDate)
    {
        // Get top 5 services by total cost
        return Service::with('vehicle')
            ->whereBetween('service_date', [$startDate, $endDate])
            ->withCount(['items as part_costs_sum' => function ($query) {
                $query->select(DB::raw('SUM(part_cost)'));
            }])
            ->orderByRaw('(labor_cost + part_costs_sum) DESC')
            ->limit(5)
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'service_date' => $service->service_date,
                    'vehicle' => [
                        'id' => $service->vehicle->id,
                        'plate_no' => $service->vehicle->plate_no,
                        'brand' => $service->vehicle->brand,
                        'model' => $service->vehicle->model,
                    ],
                    'labor_cost' => (float) $service->labor_cost,
                    'part_costs' => (float) $service->part_costs_sum,
                    'total_cost' => (float) $service->labor_cost + (float) $service->part_costs_sum
                ];
            });
    }
}