<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed, onMounted, Ref } from 'vue';
import Modal from '@/Components/Modal.vue';

// Props
const props = defineProps<{
    backups: Array<{
        name: string;
        size: number;
        last_modified: number;
    }>;
}>();

// Form işlemleri
const createForm = useForm({});
const deleteForm = useForm({
    backup_file: ''
});
const restoreForm = useForm({
    backup_file: ''
});

// Dosya yükleme formu
const uploadForm = useForm({
    backup_file: null as File | null
});

// Dosya input referansı
const fileInput = ref<HTMLInputElement | null>(null);

// Modaller
const showDeleteModal = ref(false);
const showRestoreModal = ref(false);
const selectedBackup = ref('');

// Seçilen yedek dosyası
const setSelectedBackup = (backupName: string) => {
    selectedBackup.value = backupName;
};

// Dosya boyutunu formatla
const formatFileSize = (sizeInBytes: number) => {
    if (sizeInBytes < 1024) {
        return sizeInBytes + ' bytes';
    } else if (sizeInBytes < 1024 * 1024) {
        return (sizeInBytes / 1024).toFixed(2) + ' KB';
    } else {
        return (sizeInBytes / (1024 * 1024)).toFixed(2) + ' MB';
    }
};

// Tarihi formatla
const formatDate = (timestamp: number) => {
    const date = new Date(timestamp * 1000);
    return date.toLocaleString('tr-TR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Yedek oluştur
const createBackup = () => {
    createForm.post(route('backups.create'), {
        preserveScroll: true,
        onSuccess: () => {
            // Başarılı olduğunda yapılacak işlemler
        }
    });
};

// Yedek sil
const confirmDelete = () => {
    deleteForm.backup_file = selectedBackup.value;
    showDeleteModal.value = true;
};

const deleteBackup = () => {
    deleteForm.delete(route('backups.delete'), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            selectedBackup.value = '';
        }
    });
};

// Yedeği geri yükle
const confirmRestore = () => {
    restoreForm.backup_file = selectedBackup.value;
    showRestoreModal.value = true;
};

const restoreBackup = () => {
    restoreForm.post(route('backups.restore'), {
        preserveScroll: true,
        onSuccess: () => {
            showRestoreModal.value = false;
            selectedBackup.value = '';
        }
    });
};

// Yedeği indir
const downloadBackup = (filename: string) => {
    window.location.href = route('backups.download', { filename });
};

// Yedek dosyası var mı?
const hasBackups = computed(() => props.backups.length > 0);

// Dosya seçme işlemi
const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        const file = target.files[0];
        uploadForm.backup_file = file;
    }
};

// Yedek dosyası yükleme
const uploadBackup = () => {
    uploadForm.post(route('backups.upload'), {
        preserveScroll: true,
        onSuccess: () => {
            uploadForm.backup_file = null;
            // Dosya input'unu temizle
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        }
    });
};

// Dosya input alanını açma
const openFileInput = () => {
    if (fileInput.value instanceof HTMLInputElement) {
        fileInput.value.click();
    }
};

</script>

<template>
    <Head title="Yedekleme Yönetimi" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Yedekleme Yönetimi</h2>
                <div class="flex space-x-2">
                    <form @submit.prevent="uploadBackup" class="flex items-center mr-4">
                        <input
                            type="file"
                            ref="fileInput"
                            accept=".zip"
                            class="hidden"
                            @change="handleFileChange"
                        />
                        <button
                            type="button"
                            @click="openFileInput"
                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            :disabled="uploadForm.processing"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                            </svg>
                            Yedek Yükle
                        </button>
                        <span v-if="uploadForm.backup_file" class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ uploadForm.backup_file?.name }}
                        </span>
                        <button
                            v-if="uploadForm.backup_file"
                            type="submit"
                            class="ml-2 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            :disabled="uploadForm.processing"
                        >
                            Yükle
                        </button>
                    </form>
                    <button 
                        @click="createBackup" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        :disabled="createForm.processing"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                        Yeni Yedekleme Oluştur
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div v-if="!hasBackups" class="text-center py-8">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                            </svg>
                            <p class="mt-4 text-lg font-semibold">Henüz hiç yedekleme bulunamadı</p>
                            <p class="text-gray-500 dark:text-gray-400 mt-2">Yeni bir yedekleme oluşturmak için üstteki butonu kullanabilirsiniz.</p>
                        </div>

                        <div v-else>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Dosya Adı</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Boyut</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tarih</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                        <tr v-for="backup in backups" :key="backup.name" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">
                                                {{ backup.name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ formatFileSize(backup.size) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                                {{ formatDate(backup.last_modified) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <div class="flex justify-end space-x-2">
                                                    <button @click="setSelectedBackup(backup.name); confirmRestore()" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300" title="Geri Yükle">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                        </svg>
                                                    </button>
                                                    <button @click="downloadBackup(backup.name)" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300" title="İndir">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                        </svg>
                                                    </button>
                                                    <button @click="setSelectedBackup(backup.name); confirmDelete()" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Sil">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Silme Onay Modalı -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Yedek Silme Onayı</h2>
                <div class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    <p>
                        <strong class="text-red-600 dark:text-red-400">{{ selectedBackup }}</strong> adlı yedek dosyasını silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.
                    </p>
                </div>
                <div class="flex justify-end space-x-2">
                    <button
                        @click="showDeleteModal = false"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition ease-in-out duration-150"
                    >
                        İptal
                    </button>
                    <button
                        @click="deleteBackup"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        :disabled="deleteForm.processing"
                    >
                        Sil
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Geri Yükleme Onay Modalı -->
        <Modal :show="showRestoreModal" @close="showRestoreModal = false">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Yedek Geri Yükleme Onayı</h2>
                <div class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    <p class="mb-2">
                        <strong class="text-blue-600 dark:text-blue-400">{{ selectedBackup }}</strong> adlı yedek dosyasını geri yüklemek istediğinizden emin misiniz?
                    </p>
                    <p class="font-semibold text-red-600 dark:text-red-400">
                        Bu işlem mevcut veritabanınızın üzerine yazacak ve mevcut veriler silinecektir!
                    </p>
                </div>
                <div class="flex justify-end space-x-2">
                    <button
                        @click="showRestoreModal = false"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-600 dark:hover:bg-gray-700 transition ease-in-out duration-150"
                    >
                        İptal
                    </button>
                    <button
                        @click="restoreBackup"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        :disabled="restoreForm.processing"
                    >
                        Geri Yükle
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
