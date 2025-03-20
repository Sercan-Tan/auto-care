<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    totalVehicles: Number,
    recentServices: Array,
    pendingServices: Array,
    vehicleStats: Object
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Gösterge Paneli</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl transition-all duration-300 hover:shadow-2xl hover:scale-105">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900">
                                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12C3 11.0681 3 10.6022 3.15224 10.2346 3.35523 9.74458 3.74458 9.35523 4.23463 9.15224 4.60218 9 5.06812 9 6 9H14C14.9319 9 15.3978 9 15.7654 9.15224 16.2554 9.35523 16.6448 9.74458 16.8478 10.2346 17 10.6022 17 11.0681 17 12V12C17 12.9319 17 13.3978 16.8478 13.7654 16.6448 14.2554 16.2554 14.6448 15.7654 14.8478 15.3978 15 14.9319 15 14 15H6C5.06812 15 4.60218 15 4.23463 14.8478 3.74458 14.6448 3.35523 14.2554 3.15224 13.7654 3 13.3978 3 12.9319 3 12V12zM5.09262 5.17584C5.29047 4.48337 5.38939 4.13713 5.57383 3.86837 5.81974 3.51001 6.1757 3.24151 6.58783 3.1035 6.89692 3 7.25701 3 7.97719 3H12.0228C12.743 3 13.1031 3 13.4122 3.1035 13.8243 3.24151 14.1803 3.51001 14.4262 3.86837 14.6106 4.13713 14.7095 4.48337 14.9074 5.17584V5.17584C15.2364 6.32737 15.4009 6.90313 15.3148 7.36588 15.2 7.98287 14.8022 8.51019 14.2405 8.79008 13.8192 9 13.2204 9 12.0228 9H7.97719C6.77958 9 6.18077 9 5.75949 8.79008 5.19778 8.51019 4.80002 7.98287 4.68521 7.36588 4.5991 6.90313 4.76361 6.32737 5.09262 5.17584V5.17584z"></path><path opacity="0.9" d="M13 12 13.5 12 14 12M16 8 16.5 8 17 8M3 8 3.5 8 4 8M6 12 6.5 12 7 12"></path><path d="M6 15C6.55228 15 7 15.4477 7 16L7 17C7 17.5523 6.55228 18 6 18V18C5.44772 18 5 17.5523 5 17L5 16C5 15.4477 5.44772 15 6 15V15zM14 15C14.5523 15 15 15.4477 15 16L15 17C15 17.5523 14.5523 18 14 18V18C13.4477 18 13 17.5523 13 17L13 16C13 15.4477 13.4477 15 14 15V15z"></path></svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Toplam Araç</h3>
                                    <p class="mt-1 text-3xl font-bold text-indigo-600 dark:text-indigo-400">{{ totalVehicles }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl transition-all duration-300 hover:shadow-2xl hover:scale-105">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Bu Ay Yapılan Servis</h3>
                                    <p class="mt-1 text-3xl font-bold text-green-600 dark:text-green-400">{{ vehicleStats.monthlyServices }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl transition-all duration-300 hover:shadow-2xl hover:scale-105">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Bekleyen İşlemler</h3>
                                    <p class="mt-1 text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ vehicleStats.pendingTasks }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Services -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl mb-8">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                </svg>
                                Son Servisler
                            </h3>
                            <Link :href="route('services.index')" 
                                  class="inline-flex items-center px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors duration-200">
                                <span>Tümünü Gör</span>
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </Link>
                        </div>
                        <div v-if="recentServices.length > 0" class="overflow-x-auto ring-1 ring-gray-300 dark:ring-gray-700 rounded-lg">



                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 dark:text-gray-300 uppercase rounded-tl-lg">Araç</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 dark:text-gray-300 uppercase">Tarih</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 dark:text-gray-300 uppercase">Kilometre</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 dark:text-gray-300 uppercase text-center rounded-tr-lg">İşlem</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="service in recentServices" :key="service.id"
                                        class="transition-colors duration-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="min-w-0">
                                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                                        <Link 
                                                            :href="route('vehicles.show', service.vehicle.id)"
                                                             class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400"
                                                        >
                                                            {{ service.vehicle.plate_no }}
                                                        </Link>
                                                    </div>
                                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                                        {{ service.vehicle.brand }} {{ service.vehicle.model }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ new Date(service.service_date).toLocaleDateString('tr-TR') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ new Intl.NumberFormat('tr-TR').format(service.mileage) }} km
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <Link :href="route('services.show', service.id)" 
                                                  class="inline-flex items-center px-3 py-1 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 transition-colors duration-200 rounded-md hover:bg-indigo-50 dark:hover:bg-indigo-900/50">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Detay
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Servis işlemi yoksa gösterilecek uyarı -->
                        <div v-else class="flex flex-col items-center justify-center p-8 bg-gray-50 dark:bg-gray-700/30 rounded-lg text-center space-y-4">
                            <div class="w-16 h-16 flex items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/50">
                                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Servis Kaydı Yok
                            </h4>
                            <p class="text-gray-500 dark:text-gray-400 max-w-md">
                                Henüz kayıtlı bir servis işlemi bulunmamaktadır.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pending Services -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-lg rounded-xl">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Bekleyen İşlemler
                            </h3>
                        </div>
                        <div v-if="pendingServices.length > 0" class="space-y-4">
                            <div v-for="service in pendingServices" :key="service.id"
                                 class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg transition-all duration-200 hover:shadow-md">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 flex items-center justify-center rounded-full bg-yellow-100 dark:bg-yellow-900">
                                            <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ service.vehicle.plate_no }} - {{ service.vehicle.brand }} {{ service.vehicle.model }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            Planlanan: {{ new Date(service.service_date).toLocaleDateString('tr-TR') }}
                                        </div>
                                    </div>
                                </div>
                                <Link :href="route('services.show', service.id)" 
                                      class="inline-flex items-center px-3 py-1 text-sm font-medium text-yellow-600 dark:text-yellow-400 hover:text-yellow-900 dark:hover:text-yellow-300 transition-colors duration-200 rounded-md hover:bg-yellow-50 dark:hover:bg-yellow-900/50">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Detay
                                </Link>
                            </div>
                        </div>
                        
                        <!-- Bekleyen işlem yoksa gösterilecek uyarı -->
                        <div v-else class="flex flex-col items-center justify-center p-8 bg-gray-50 dark:bg-gray-700/30 rounded-lg text-center space-y-4">
                            <div class="w-16 h-16 flex items-center justify-center rounded-full bg-green-100 dark:bg-green-900/50">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Bekleyen İşlem Yok
                            </h4>
                            <p class="text-gray-500 dark:text-gray-400 max-w-md">
                                Şu anda bekleyen servis işlemi bulunmamaktadır. Tüm araçların bakımı yapılmış durumda!
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
