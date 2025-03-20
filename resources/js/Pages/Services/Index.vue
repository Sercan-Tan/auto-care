<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/Components/Pagination.vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';

const props = defineProps({
    services: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            startDate: '',
            endDate: ''
        })
    }
});

const search = ref(props.filters.search);
const startDate = ref(props.filters.startDate);
const endDate = ref(props.filters.endDate);
const confirmingDeletion = ref(false);
const serviceToDelete = ref(null);

// Aramayı ve tarih filtresini yönet
const handleFilters = debounce(() => {
    router.get(route('services.index'), 
        { 
            search: search.value,
            startDate: startDate.value,
            endDate: endDate.value 
        }, 
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
}, 300);

watch([search, startDate, endDate], handleFilters);

const confirmServiceDeletion = (service) => {
    serviceToDelete.value = service;
    confirmingDeletion.value = true;
};

const deleteService = () => {
    router.delete(route('services.destroy', serviceToDelete.value.id), {
        onSuccess: () => closeModal(),
    });
};

const closeModal = () => {
    confirmingDeletion.value = false;
    serviceToDelete.value = null;
};

// Tarihi formatla
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('tr-TR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// Kilometreyi basamaklı formata dönüştür
const formatMileage = (mileage) => {
    return new Intl.NumberFormat('tr-TR').format(mileage);
};
</script>

<template>
    <Head title="Servisler" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Servisler</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Plaka Arama -->
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
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white dark:bg-gray-700 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm dark:text-gray-100"
                        />
                    </div>

                    <!-- Başlangıç Tarihi -->
                    <div>
                        <input
                            v-model="startDate"
                            type="date"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white dark:bg-gray-700 dark:border-gray-600 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                        />
                    </div>

                    <!-- Bitiş Tarihi -->
                    <div>
                        <input
                            v-model="endDate"
                            type="date"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md leading-5 bg-white dark:bg-gray-700 dark:border-gray-600 text-gray-700 dark:text-gray-200 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
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
                                            Araç Bilgisi
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Servis Tarihi
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            KM Bilgisi
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            İşlem Sayısı
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">İşlemler</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="service in services.data" :key="service.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <Link 
                                                :href="route('vehicles.show', service.vehicle.id)"
                                                class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:text-blue-600 dark:hover:text-blue-400"
                                            >
                                                {{ service.vehicle.plate_no }}
                                            </Link>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ service.vehicle.brand }} {{ service.vehicle.model }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ formatDate(service.service_date) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ formatMileage(service.mileage) }} km
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">
                                                {{ service.items.length }} işlem
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex justify-end space-x-2">
                                                <Link 
                                                    :href="route('services.show', service.id)" 
                                                    class="inline-flex items-center px-3 py-2 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900 rounded-md transition-colors duration-150 focus:outline-none"
                                                    title="Detay"
                                                >
                                                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                    Detay
                                                </Link>
                                                <button
                                                    @click="confirmServiceDeletion(service)"
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
                        <Pagination :links="services.links" class="mt-6" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Silme Onay Modalı -->
        <Modal :show="confirmingDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Servis kaydını silmek istediğinize emin misiniz?
                </h2>

                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    Bu işlem geri alınamaz. Servis kaydını sildiğinizde, tüm işlem kayıtları da silinecektir.
                </p>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeModal">
                        İptal
                    </SecondaryButton>

                    <DangerButton @click="deleteService">
                        Servis Kaydını Sil
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>