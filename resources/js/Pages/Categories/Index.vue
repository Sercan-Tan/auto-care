<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    categories: {
        type: Array,
        required: true
    }
});

// Ana kategorileri filtrele
const rootCategories = computed(() => {
    // Sadece parent_id'si null olan (kök) kategorileri döndür
    return props.categories.filter(category => category.parent_id === null);
});

const confirmingDeletion = ref(false);
const categoryToDelete = ref(null);

const confirmCategoryDeletion = (category) => {
    categoryToDelete.value = category;
    confirmingDeletion.value = true;
};

const deleteCategory = () => {
    router.delete(route('categories.destroy', categoryToDelete.value.id), {
        onSuccess: () => closeModal(),
    });
};

const closeModal = () => {
    confirmingDeletion.value = false;
    categoryToDelete.value = null;
};

</script>

<template>
    <Head title="Kategoriler" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Kategoriler</h2>
                <Link 
                    :href="route('categories.create')" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    title="Yeni Kategori Ekle"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Yeni Kategori Ekle
                </Link>
            </div>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <div class="overflow-x-auto ring-1 ring-gray-300 dark:ring-gray-700 rounded-lg">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                            Kategori Adı
                                        </th>
                                        <th scope="col" class="relative px-6 py-3">
                                            <span class="sr-only">İşlemler</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    <!-- Kök kategoriler ve onların alt kategorileri, recursive şablonla -->
                                    <template v-for="category in rootCategories" :key="category.id">
                                        <!-- Ana kategori satırı -->
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">

                                                    <Link :href="route('categories.edit', category.id)">
                                                        <span :class="{'font-semibold': category.children && category.children.length}">
                                                            {{ category.name }}
                                                        </span>
                                                    </Link>

                                                    <span v-if="category.children && category.children.length" 
                                                        class="ml-2 text-xs text-gray-500">
                                                        ({{ category.children.length }})
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <Link :href="route('categories.edit', category.id)" 
                                                    class="inline-flex items-center px-3 py-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900 rounded-md transition-colors duration-150 focus:outline-none"
                                                    title="Düzenle">
                                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                        Düzenle
                                                </Link>
                                                <button @click="confirmCategoryDeletion(category)" 
                                                    class="inline-flex items-center px-3 py-2 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900 rounded-md transition-colors duration-150 focus:outline-none"
                                                    title="Sil">
                                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Sil
                                                </button>
                                            </td>
                                        </tr>
                                        
                                        <!-- Alt kategorileri göster -->
                                        <template v-if="category.children && category.children.length">
                                            <tr v-for="child in category.children" :key="child.id">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="ml-5 flex items-center">
                                                            <span class="text-gray-400 mr-2">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M13 8L17.6464 12.6464C17.8417 12.8417 17.8417 13.1583 17.6464 13.3536L13 18"></path><path d="M2 2V9C2 10.0609 2.42143 11.0783 3.17157 11.8284C3.92172 12.5786 4.93913 13 6 13H17.5"></path></svg>
                                                            </span>
                                                            <Link :href="route('categories.edit', child.id)">
                                                                <span :class="{'font-semibold': child.children && child.children.length}">
                                                                    {{ child.name }}
                                                                </span>
                                                            </Link>
                                                            <span v-if="child.children && child.children.length" 
                                                                class="ml-2 text-xs text-gray-500">
                                                                ({{ child.children.length }})
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <Link :href="route('categories.edit', child.id)" 
                                                        class="inline-flex items-center px-3 py-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 dark:hover:bg-indigo-900 rounded-md transition-colors duration-150 focus:outline-none"
                                                        title="Düzenle">
                                                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                            Düzenle
                                                    </Link>
                                                    <button @click="confirmCategoryDeletion(child)" 
                                                        class="inline-flex items-center px-3 py-2 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900 rounded-md transition-colors duration-150 focus:outline-none"
                                                        title="Sil">
                                                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                            Sil
                                                    </button>
                                                </td>
                                            </tr>
                                            
                                            <!-- Alt kategorilerin alt kategorileri -->
                                            <template v-for="child in category.children" :key="'subsub-'+child.id">
                                                <template v-if="child.children && child.children.length">
                                                    <tr v-for="subchild in child.children" :key="subchild.id">
                                                        <td class="px-6 py-4 whitespace-nowrap">
                                                            <div class="flex items-center">
                                                                <div class="ml-10 flex items-center">
                                                                    <span class="text-gray-400 mr-2">└─</span>
                                                                    <span>{{ subchild.name }}</span>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                            <Link :href="route('categories.edit', subchild.id)" 
                                                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">
                                                                Düzenle
                                                            </Link>
                                                            <button @click="confirmCategoryDeletion(subchild)" 
                                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                                Sil
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </template>
                                            </template>
                                        </template>
                                    </template>
                                    
                                    <!-- Hiç kategori yoksa gösterilecek mesaj -->
                                    <tr v-if="rootCategories.length === 0">
                                        <td colspan="2" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                            Henüz kategori bulunmuyor. Yeni bir kategori ekleyebilirsiniz.
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

    <!-- Silme Onay Modalı -->
    <Modal :show="confirmingDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Kategoriyi silmek istediğinize emin misiniz?
                </h2>

                <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                    Bu işlem geri alınamaz. Kategoriyi sildiğinizde, tüm işlem kayıtlarından da silinecektir.
                </p>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeModal">
                        İptal
                    </SecondaryButton>

                    <DangerButton @click="deleteCategory">
                        Kategoriyi Sil
                    </DangerButton>
                </div>
            </div>
        </Modal>
</template>