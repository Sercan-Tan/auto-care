<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

// Tarih formatlama fonksiyonu
const formatDate = (dateString) => {
    if (!dateString) return 'Tarih yok';
    
    try {
        const date = new Date(dateString);
        
        // Geçerli bir tarih kontrolü
        if (isNaN(date.getTime())) {
            return 'Geçersiz tarih';
        }
        
        return date.toLocaleDateString('tr-TR', {
            year: 'numeric', 
            month: 'long', 
            day: 'numeric'
        });
    } catch (error) {
        return 'Tarih hatası';
    }
};

const props = defineProps({
    vehicles: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({
            search: ''
        })
    }
});

const search = ref(props.filters.search);
const confirmingDeletion = ref(false);
const vehicleToDelete = ref(null);

watch(search, debounce((value) => {
    router.get(route('vehicles.index'), { search: value }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 300));

const confirmVehicleDeletion = (vehicle) => {
    vehicleToDelete.value = vehicle;
    confirmingDeletion.value = true;
};

const deleteVehicle = () => {
    router.delete(route('vehicles.destroy', vehicleToDelete.value.id), {
        onSuccess: () => closeModal(),
    });
};

const closeModal = () => {
    confirmingDeletion.value = false;
    vehicleToDelete.value = null;
};
</script>

<template>
    <Head title="Araçlar" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Araçlar</h2>
                <Link 
                    :href="route('vehicles.create')" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Yeni Araç Ekle                    
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="mb-6 grid grid-cols-1 gap-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Plaka, marka, model veya araç sahibi adını ara..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white dark:bg-gray-700 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        />
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
    
                        <div class="overflow-x-auto ring-1 ring-gray-300 dark:ring-gray-700 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Plaka
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Marka/Model
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Araç Sahibi
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Son Servis
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">İşlemler</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="vehicle in vehicles.data" :key="vehicle.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Link 
                                                :href="route('vehicles.show', vehicle.id)"
                                                class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400"
                                            >
                                                {{ vehicle.plate_no }}
                                            </Link>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ vehicle.brand }} {{ vehicle.model }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ vehicle.year }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ vehicle.owner_name }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ vehicle.owner_phone }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div v-if="vehicle.services && vehicle.services.length" class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ formatDate(vehicle.services[vehicle.services.length - 1].service_date) }}
                                            </div>
                                            <div v-else class="text-sm text-gray-500 dark:text-gray-400">
                                                Servis kaydı yok
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <Link 
                                                    :href="route('vehicles.show', vehicle.id)" 
                                                    class="inline-flex items-center px-3 py-2 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900 rounded-md transition-colors duration-150 focus:outline-none"
                                                    title="Detay"
                                                >
                                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    Detay
                                                </Link>
                                                <Link 
                                                    :href="route('services.create', vehicle.id)" 
                                                    class="inline-flex items-center px-3 py-2 text-green-600 dark:text-green-400 hover:bg-green-100 dark:hover:bg-green-900 rounded-md transition-colors duration-150 focus:outline-none"
                                                    title="Servis Ekle"
                                                >
                                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                    </svg>
                                                    Servis Ekle
                                                </Link>
                                                <button
                                                    @click="confirmVehicleDeletion(vehicle)"
                                                    class="inline-flex items-center px-3 py-2 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900 rounded-md transition-colors duration-150 focus:outline-none"
                                                    title="Sil"
                                                >
                                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    Sil
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <Pagination :links="vehicles.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Silme Onay Modalı -->
        <Modal :show="confirmingDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Aracı silmek istediğinize emin misiniz?
                </h2>

                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    Bu işlem geri alınamaz. Aracı sildiğinizde, tüm servis kayıtları da silinecektir.
                </p>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeModal">
                        İptal
                    </SecondaryButton>

                    <DangerButton @click="deleteVehicle">
                        Aracı Sil
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>