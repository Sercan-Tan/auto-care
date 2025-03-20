<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    service: {
        type: Object,
        required: true
    }
});

// İşlemleri kategorilere göre gruplandır
const groupedItems = computed(() => {
    if (!props.service.items || props.service.items.length === 0) return [];
    
    // Önce tüm kategorileri ve parent_id'lerini haritala
    const categoryMap = {};
    const parentCategories = new Set();
    
    // Alt kategorileri parent_id'lerine göre grupla
    props.service.items.forEach(item => {
        if (item.category.parent_id) {
            parentCategories.add(item.category.parent_id);
            if (!categoryMap[item.category.parent_id]) {
                categoryMap[item.category.parent_id] = {
                    id: item.category.parent_id,
                    name: item.category.parent?.name || 'Diğer',
                    items: []
                };
            }
            categoryMap[item.category.parent_id].items.push(item);
        }
    });
    
    // Parent_id'si olmayan ana kategorileri ekle
    props.service.items.forEach(item => {
        if (!item.category.parent_id) {
            // Eğer bu kategori başka bir kategorinin parent'ı ise, sadece alt öğelerini göster
            if (!parentCategories.has(item.category.id)) {
                if (!categoryMap[item.category.id]) {
                    categoryMap[item.category.id] = {
                        id: item.category.id,
                        name: item.category.name,
                        items: []
                    };
                }
                categoryMap[item.category.id].items.push(item);
            }
        }
    });
    
    // Object.values ile categoryMap'i diziye çevir
    return Object.values(categoryMap);
});

// Tarihi gün adıyla birlikte formatla
const formatDate = (date) => {
    const options = { 
        weekday: 'long', // Gün adı
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    };
    return new Date(date).toLocaleDateString('tr-TR', options);
};

// Kilometreyi basamaklı formata dönüştür
const formatMileage = (mileage) => {
    return new Intl.NumberFormat('tr-TR').format(mileage);
};
</script>

<template>
    <Head title="Servis Detay" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Servis Detay - 
                    <Link 
                        :href="route('vehicles.show', service.vehicle.id)"
                        class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 hover:underline"
                    >
                        {{ service.vehicle.plate_no }}
                    </Link>
                </h2>                
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Servis Bilgileri -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Plaka No</p>
                                <Link 
                                    :href="route('vehicles.show', service.vehicle.id)"
                                    class="font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                                >
                                    {{ service.vehicle.plate_no }}
                                </Link>            
                            </div>

                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Marka / Model</p>
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ service.vehicle.brand }} {{ service.vehicle.model }}
                                </p>
                            </div>

                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Servis Tarihi</p>
                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ formatDate(service.service_date) }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Kilometre</p>
                                <p class="font-medium text-gray-900 dark:text-gray-100">{{ formatMileage(service.mileage) }} km</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Servis İşlemleri -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6">Yapılan İşlemler</h3>

                            <div class="flex space-x-4">
                                <Link
                                    :href="route('services.edit', service.id)"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-600 focus:bg-blue-600 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                    Düzenle
                                </Link>
                            </div>
                        </div>

                        <div class="space-y-8">
                            <div v-for="group in groupedItems" :key="group.id" class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                                <!-- Kategori Başlığı -->
                                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 border-b border-gray-200 dark:border-gray-600">
                                    <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ group.name }}
                                    </h4>
                                </div>
                                
                                <!-- Kategori İşlemleri -->
                                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <div v-for="item in group.items" :key="item.id" class="p-4">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center">
                                                <span class="mr-3">
                                                    <svg v-if="item.completed" class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    <svg v-else class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </span>
                                                <div>
                                                    <h5 class="font-medium text-gray-900 dark:text-gray-100">{{ item.category.name }}</h5>
                                                    <p v-if="item.notes" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                                        {{ item.notes }}
                                                    </p>
                                                </div>
                                            </div>
                                            <span class="px-3 py-1 text-sm rounded-full" 
                                                  :class="item.completed ? 
                                                    'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 
                                                    'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'">
                                                {{ item.completed ? 'Tamamlandı' : 'Bekliyor' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>