<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, onMounted, watch } from 'vue';
import { onBeforeUnmount } from 'vue';

const props = defineProps({
    totals: Object,
    monthlyCosts: Array,
    topServices: Array,
    filters: Object
});

// Active filter period
const activePeriod = ref(props.filters.period);
const startDate = ref(props.filters.startDate);
const endDate = ref(props.filters.endDate);

// Formatting functions
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY',
        minimumFractionDigits: 2
    }).format(amount || 0);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('tr-TR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
};

// Chart references
const barChartRef = ref(null);
const pieChartRef = ref(null);
let barChart = null;
let pieChart = null;

// Apply filters
const applyFilter = (period) => {
    activePeriod.value = period;
    
    const params = { period };
    
    if (period === 'custom') {
        params.start_date = startDate.value;
        params.end_date = endDate.value;
    }
    
    router.get(route('costs.index'), params, {
        preserveState: true,
        only: ['totals', 'monthlyCosts', 'topServices', 'filters']
    });
};

// Apply custom date filter
const applyCustomDateFilter = () => {
    if (startDate.value && endDate.value) {
        applyFilter('custom');
    }
};

// Create chart functions
const createBarChart = () => {
    if (!props.monthlyCosts || !props.monthlyCosts.length) return;
    
    const ctx = document.getElementById('costs-bar-chart');
    if (!ctx) return;
    
    const labels = props.monthlyCosts.map(item => item.month);
    const laborData = props.monthlyCosts.map(item => item.labor);
    const partsData = props.monthlyCosts.map(item => item.parts);
    
    import('chart.js').then(({ Chart, CategoryScale, LinearScale, BarController, BarElement, Tooltip, Legend }) => {
        Chart.register(CategoryScale, LinearScale, BarController, BarElement, Tooltip, Legend);
        
        if (barChart) {
            barChart.destroy();
        }
        
        barChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'İşçilik Ücreti',
                        data: laborData,
                        backgroundColor: 'rgba(34, 197, 94, 0.7)', // Green for labor costs
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1
                    },
                    {
                        label: 'Parça Ücreti',
                        data: partsData,
                        backgroundColor: 'rgba(59, 130, 246, 0.7)', // Blue for part costs
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        stacked: false,
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        stacked: false,
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('tr-TR') + ' ₺';
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(context.raw);
                                return label;
                            }
                        }
                    }
                }
            }
        });
    });
};

const createPieChart = () => {
    const ctx = document.getElementById('costs-pie-chart');
    if (!ctx) {
        console.error('Pie chart canvas element not found');
        return;
    }
    
    // Check if we have valid data to display
    if (!props.totals || typeof props.totals.laborCosts === 'undefined' || typeof props.totals.partCosts === 'undefined') {
        console.error('Invalid or missing data for pie chart', props.totals);
        // Display a message in the chart area if no data
        const container = ctx.parentElement;
        if (container) {
            const message = document.createElement('div');
            message.className = 'flex flex-col items-center justify-center h-full text-gray-500 dark:text-gray-400';
            message.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>Gösterilecek veri bulunamadı</p>
            `;
            container.innerHTML = '';
            container.appendChild(message);
        }
        return;
    }
    
    // Make sure we have numeric values to work with
    const laborCost = parseFloat(props.totals.laborCosts || 0);
    const partCost = parseFloat(props.totals.partCosts || 0);
    
    // Only create chart if there's data to show
    const totalCost = laborCost + partCost;
    if (totalCost === 0) {
        console.log('No cost data to display (both values are zero)');
        // Display a message in the chart area
        const container = ctx.parentElement;
        if (container) {
            const message = document.createElement('div');
            message.className = 'flex flex-col items-center justify-center h-full text-gray-500 dark:text-gray-400';
            message.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>Seçilen tarih aralığında maliyet verisi bulunamadı</p>
            `;
            container.innerHTML = '';
            container.appendChild(message);
        }
        return;
    }
    
    console.log('Creating pie chart with data:', { laborCost, partCost });
    
    // Fixed: Explicitly import and register all required Chart.js components
    import('chart.js').then((ChartModule) => {
        const { Chart, ArcElement, Tooltip, Legend, PieController } = ChartModule;
        
        // Register all required components
        Chart.register(ArcElement, Tooltip, Legend, PieController);
        
        if (pieChart) {
            pieChart.destroy();
        }
        
        pieChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['İşçilik Ücreti', 'Parça Ücreti'],
                datasets: [{
                    data: [laborCost, partCost],
                    backgroundColor: [
                        // Changed colors: Green for labor, blue for parts
                        'rgba(34, 197, 94, 0.7)', // Green for labor costs
                        'rgba(59, 130, 246, 0.7)'  // Blue for part costs
                    ],
                    borderColor: [
                        'rgb(34, 197, 94)', // Green border
                        'rgb(59, 130, 246)'  // Blue border
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value)} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }).catch(error => {
        console.error('Error loading Chart.js:', error);
    });
};

// Initialize charts when component is mounted
onMounted(() => {
    createBarChart();
    createPieChart();
});

// Create date range title for display
const dateRangeTitle = computed(() => {
    switch(props.filters.period) {
        case 'current_month':
            return 'Bu Ay';
        case 'last_month':
            return 'Geçen Ay';
        case 'last_3_months':
            return 'Son 3 Ay';
        case 'last_6_months':
            return 'Son 6 Ay';
        case 'current_year':
            return 'Bu Yıl';
        case 'custom':
            return `${formatDate(props.filters.startDate)} - ${formatDate(props.filters.endDate)}`;
        default:
            return 'Tüm Tarihler';
    }
});

// Watch for prop changes and update charts
const updateCharts = () => {
    // Use setTimeout to ensure DOM is ready
    setTimeout(() => {
        createBarChart();
        createPieChart();
    }, 100);
};

// Detect prop changes and update charts
watch(() => props.monthlyCosts, updateCharts, { deep: true });
watch(() => props.totals, updateCharts, { deep: true });

// Clean up charts when component is destroyed
onBeforeUnmount(() => {
    if (barChart) {
        barChart.destroy();
    }
    if (pieChart) {
        pieChart.destroy();
    }
});
</script>

<template>
    <Head title="Maliyet Analizi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Maliyet Analizi
                </h2>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ dateRangeTitle }}
                </span>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filter Buttons -->
                <div class="mb-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Tarih Filtresi</h3>
                    </div>
                    
                    <div class="p-4">
                        <div class="flex flex-wrap gap-3">
                            <button 
                                @click="applyFilter('current_month')" 
                                :class="[
                                    'px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200',
                                    activePeriod === 'current_month' 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                                ]"
                            >
                                Bu Ay
                            </button>
                            <button 
                                @click="applyFilter('last_month')" 
                                :class="[
                                    'px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200',
                                    activePeriod === 'last_month' 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                                ]"
                            >
                                Geçen Ay
                            </button>
                            <button 
                                @click="applyFilter('last_3_months')" 
                                :class="[
                                    'px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200',
                                    activePeriod === 'last_3_months' 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                                ]"
                            >
                                Son 3 Ay
                            </button>
                            <button 
                                @click="applyFilter('last_6_months')" 
                                :class="[
                                    'px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200',
                                    activePeriod === 'last_6_months' 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                                ]"
                            >
                                Son 6 Ay
                            </button>
                            <button 
                                @click="applyFilter('current_year')" 
                                :class="[
                                    'px-4 py-2 rounded-md text-sm font-medium transition-colors duration-200',
                                    activePeriod === 'current_year' 
                                        ? 'bg-indigo-600 text-white' 
                                        : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600'
                                ]"
                            >
                                Bu Yıl
                            </button>
                            <div class="flex items-center gap-2">
                                <input 
                                    v-model="startDate" 
                                    type="date" 
                                    class="px-3 py-2 rounded-md text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300"
                                />
                                <span class="text-gray-500 dark:text-gray-400">-</span>
                                <input 
                                    v-model="endDate" 
                                    type="date" 
                                    class="px-3 py-2 rounded-md text-sm border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300"
                                />
                                <button 
                                    @click="applyCustomDateFilter" 
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium transition-colors duration-200"
                                >
                                    Uygula
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <!-- İşçilik Ücreti -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">İşçilik Ücreti</p>
                                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ formatCurrency(totals.laborCosts) }}
                                    </p>
                                </div>
                                <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Toplam maliyetin <span class="font-semibold">{{ Math.round((totals.laborCosts / totals.totalCosts) * 100) || 0 }}%</span>'i
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Parça Ücreti -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Parça Ücreti</p>
                                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ formatCurrency(totals.partCosts) }}
                                    </p>
                                </div>
                                <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Toplam maliyetin <span class="font-semibold">{{ Math.round((totals.partCosts / totals.totalCosts) * 100) || 0 }}%</span>'i
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Toplam Maliyet -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl hover:shadow-xl transition-shadow">
                        <div class="p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Toplam Maliyet</p>
                                    <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ formatCurrency(totals.totalCosts) }}
                                    </p>
                                </div>
                                <div class="p-3 bg-emerald-100 dark:bg-emerald-900/30 rounded-full">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    Seçilen tarih aralığındaki toplam maliyet
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Bar Chart -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Aylık Maliyet Analizi</h3>
                        </div>
                        <div class="p-6">
                            <div class="h-80">
                                <canvas id="costs-bar-chart" ref="barChartRef"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pie Chart -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl">
                        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Maliyet Dağılımı</h3>
                        </div>
                        <div class="p-6">
                            <div class="h-80">
                                <canvas id="costs-pie-chart" ref="pieChartRef"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Top Services Table -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl">
                    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">En Yüksek Maliyetli Servisler</h3>
                    </div>
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Araç
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Servis Tarihi
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            İşçilik Ücreti
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Parça Ücreti
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Toplam
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">Detay</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="service in topServices" :key="service.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ service.vehicle.plate_no }}</div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">{{ service.vehicle.brand }} {{ service.vehicle.model }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">{{ formatDate(service.service_date) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">{{ formatCurrency(service.labor_cost) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">{{ formatCurrency(service.part_costs) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ formatCurrency(service.total_cost) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a :href="route('services.show', service.id)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">
                                                Detay
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>