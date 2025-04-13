<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    vehicle: {
        type: Object,
        required: true
    }
});

// Servisleri tarihe göre sırala (en son yapılan en üstte)
const sortedServices = computed(() => {
    if (!props.vehicle.services || !props.vehicle.services.length) return [];
    
    // Tarihe göre sıralama yap (en yeni en üstte)
    return [...props.vehicle.services].sort((a, b) => {
        return new Date(b.service_date) - new Date(a.service_date);
    });
});

// Servisler için accordion yapısı için durum takibi
const openServiceId = ref(null);

const toggleService = (serviceId) => {
    if (openServiceId.value === serviceId) {
        openServiceId.value = null;
    } else {
        openServiceId.value = serviceId;
    }
};

// İşlemleri kategorilere göre gruplandır
const groupServiceItemsByCategory = (items) => {
    if (!items || items.length === 0) return [];
    
    // Sonuç grupları listesi
    const result = [];
    
    // Zaten grup haline getirilmiş öğeleri izle
    const groupedItems = new Set();
    
    // Ana kategori öğeleri - parent_id'si olmayanlar
    const rootItems = items.filter(item => item.category && !item.category.parent_id);
    
    // Alt kategori öğeleri - parent_id'si olanlar
    const childItems = items.filter(item => item.category && item.category.parent_id);
    
    // 1. ADIM: Ana kategorileri doğrudan öğeleriyle listele (gruplamadan)
    rootItems.forEach(item => {
        // Her ana kategori öğesini doğrudan kendi kategorisiyle listele
        result.push({
            id: item.category.id + '_item_' + item.id, // Benzersiz bir ID oluştur
            name: item.category.name,
            items: [item],
            isDirectItem: true // Bu bir ana kategori öğesi
        });
        groupedItems.add(item.id);
    });
    
    // 2. ADIM: Alt kategorileri doğru üst kategori gruplarına yerleştir
    const parentGroups = {};
    
    childItems.forEach(item => {
        if (groupedItems.has(item.id)) return; // Zaten işlenmiş öğeleri atla
        
        const category = item.category;
        const parentId = category.parent_id;
        
        // Üst kategori adını bul
        let parentName = "Diğer İşlemler"; // varsayılan değer
        
        // Eğer parent objesi gelmişse (controller'dan), adını kullan
        if (category.parent && category.parent.name) {
            parentName = category.parent.name;
        }
        
        // Bu parent için bir grup var mı kontrol et
        if (!parentGroups[parentId]) {
            parentGroups[parentId] = {
                id: parentId,
                name: parentName,
                items: []
            };
        }
        
        // Bu öğeyi parent grubuna ekle
        parentGroups[parentId].items.push(item);
        groupedItems.add(item.id);
    });
    
    // Parent gruplarını sonuçlara ekle
    Object.values(parentGroups).forEach(group => {
        if (group.items.length > 0) {
            result.push(group);
        }
    });
    
    // 3. ADIM: Kategorisi olmayan kalan öğeler
    const uncategorizedItems = items.filter(item => !groupedItems.has(item.id));
    
    if (uncategorizedItems.length > 0) {
        result.push({
            id: 'uncategorized',
            name: 'Kategorilendirilmemiş',
            items: uncategorizedItems
        });
    }
    
    return result;
};

// Tarihi formatla
const formatDate = (dateString) => {
    if (!dateString) return '';
    
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        weekday: 'long'
    };
    
    return new Date(dateString).toLocaleDateString('tr-TR', options);
};

// Ücretleri para birimi formatına dönüştür
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('tr-TR', {
        style: 'currency',
        currency: 'TRY',
        minimumFractionDigits: 2
    }).format(amount || 0);
};

// Toplam maliyeti hesapla (işçilik + parça ücretleri)
const calculateTotalCost = (service) => {
    // İşçilik ücreti
    const laborCost = parseFloat(service.labor_cost || 0);
    
    // Tüm parça ücretlerinin toplamı
    const partsCost = service.items.reduce((total, item) => {
        return total + parseFloat(item.part_cost || 0);
    }, 0);
    
    return laborCost + partsCost;
};
</script>

<template>
    <Head :title="vehicle.plate_no + ' - Araç Detayı'" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Araç Detayı - {{ vehicle.plate_no }}
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Araç Bilgileri Bölümü -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Araç Bilgileri
                            </h3>
                            <div class="flex space-x-3">
                                <Link
                                    :href="route('vehicles.edit', vehicle.id)"
                                    class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm font-medium text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                    Düzenle
                                </Link>
                                <Link
                                    :href="route('services.create', vehicle.id)"
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white rounded-md text-sm font-medium shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Yeni Servis Ekle
                                </Link>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Plaka</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ vehicle.plate_no }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Marka/Model</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ vehicle.brand }} {{ vehicle.model }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Yıl</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ vehicle.year }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Şasi No</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ vehicle.chassis_no }}</dd>
                                    </div>
                                </dl>
                            </div>
                            <div>
                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Araç Sahibi</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ vehicle.owner_name }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Telefon</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ vehicle.owner_phone }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">E-posta</dt>
                                        <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ vehicle.owner_email || '-' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Maliyet Özeti Bölümü -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Maliyet Özeti
                        </h3>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                                    <div class="flex flex-col">
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Toplam İşçilik Maliyeti</div>
                                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            {{ formatCurrency(vehicle.total_labor_cost) }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow">
                                    <div class="flex flex-col">
                                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Toplam Parça Maliyeti</div>
                                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            {{ formatCurrency(vehicle.total_parts_cost) }}
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg shadow">
                                    <div class="flex flex-col">
                                        <div class="text-sm font-medium text-indigo-600 dark:text-indigo-300">Genel Toplam</div>
                                        <div class="mt-1 text-2xl font-bold text-indigo-700 dark:text-indigo-300">
                                            {{ formatCurrency(vehicle.total_cost) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Servis Geçmişi Bölümü - Modern Tasarım -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                            Servis Geçmişi
                        </h3>
                        
                        <div v-if="sortedServices.length > 0" class="space-y-4">
                            <!-- Servis Kartları - Accordion -->
                            <div v-for="service in sortedServices" :key="service.id" 
                                 class="border dark:border-gray-700 rounded-lg overflow-hidden transition-all duration-200 ease-in-out"
                                 :class="{ 'shadow-md': openServiceId === service.id }">
                                
                                <!-- Servis Başlık (her zaman gözükür) -->
                                <div @click="toggleService(service.id)" 
                                     class="flex justify-between items-center p-4 cursor-pointer transition-colors"
                                     :class="{ 
                                         'bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-900/20 dark:hover:bg-indigo-900/30': openServiceId === service.id,
                                         'hover:bg-gray-50 dark:hover:bg-gray-700': openServiceId !== service.id 
                                     }">
                                    
                                    <div class="flex items-center space-x-4">
                                        <div class="flex-shrink-0 w-10 h-10 bg-indigo-100 dark:bg-indigo-800 rounded-full flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 dark:text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100">
                                                {{ formatDate(service.service_date) }}
                                            </h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                                Kilometre: {{ service.mileage.toLocaleString('tr-TR') }} km
                                                <!-- İşlem sayısı -->
                                                <span class="ml-2 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full text-xs">
                                                    {{ service.items?.length || 0 }} işlem
                                                </span>
                                                <!-- Toplam Tutar -->
                                                <span class="ml-2 bg-green-100 dark:bg-green-900 px-2 py-0.5 rounded-full text-xs text-green-800 dark:text-green-300">
                                                    {{ formatCurrency(calculateTotalCost(service)) }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-3">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ openServiceId === service.id ? 'Gizle' : 'Detay' }}
                                        </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400 transition-transform duration-200" 
                                             :class="{ 'transform rotate-180': openServiceId === service.id }"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                        <Link
                                            :href="route('services.show', service.id)"
                                            class="inline-flex items-center px-3 py-1 text-sm bg-blue-600 dark:bg-blue-500 hover:bg-blue-700 dark:hover:bg-blue-400 text-white rounded-md"
                                            @click.stop
                                        >
                                            Görüntüle
                                        </Link>
                                    </div>
                                </div>
                                
                                <!-- Servis Detayları (açılınca gözükür) -->
                                <div v-if="openServiceId === service.id" class="p-4 border-t dark:border-gray-700 bg-white dark:bg-gray-800">
                                    <div v-if="service.items && service.items.length > 0">
                                        <!-- Kategorilere göre gruplandırılmış işlemler -->
                                        <div v-for="(group, groupIndex) in groupServiceItemsByCategory(service.items)" 
                                             :key="'group-' + group.id" 
                                             class="mb-4 last:mb-0">
                                            
                                            <!-- Kategori Başlığı -->
                                            <div class="flex items-center mb-2 pb-1 border-b dark:border-gray-700">
                                                <span class="font-medium text-gray-800 dark:text-gray-200">{{ group.name }}</span>
                                                <span class="ml-2 text-xs text-gray-500 dark:text-gray-400">({{ group.items.length }} işlem)</span>
                                            </div>
                                            
                                            <!-- Kategori altındaki işlemler -->
                                            <div class="space-y-2 pl-4">
                                                <div v-for="item in group.items" :key="item.id" 
                                                     class="flex items-start space-x-3 py-1">
                                                    
                                                    <!-- Tamamlanma durumu ikonu -->
                                                    <div class="flex-shrink-0 mt-0.5">
                                                        <div v-if="item.completed" class="w-5 h-5 bg-green-100 dark:bg-green-900/20 rounded-full flex items-center justify-center">
                                                            <svg class="w-3 h-3 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                        </div>
                                                        <div v-else class="w-5 h-5 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center">
                                                            <svg class="w-3 h-3 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- İşlem adı ve notu -->
                                                    <div class="flex flex-col">
                                                        <span class="text-sm text-gray-800 dark:text-gray-200">
                                                            {{ item.category?.name }}
                                                        </span>
                                                        <span v-if="item.notes" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                            {{ item.notes }}
                                                        </span>
                                                        <span v-if="item.part_cost && parseFloat(item.part_cost) > 0" class="text-xs font-medium text-blue-600 dark:text-blue-400 mt-1 block">
                                                            Parça Ücreti: {{ formatCurrency(item.part_cost) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-center text-gray-500 dark:text-gray-400 py-4">
                                        Bu servis kaydında işlem bulunmuyor.
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="text-center py-8">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400">Henüz servis kaydı bulunmuyor.</p>
                            <Link
                                :href="route('services.create', vehicle.id)"
                                class="mt-4 inline-flex items-center px-4 py-2 bg-green-600 dark:bg-green-500 hover:bg-green-700 dark:hover:bg-green-400 text-white rounded-md"
                            >
                                Yeni Servis Ekle
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>