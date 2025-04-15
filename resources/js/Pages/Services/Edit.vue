<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref, computed, defineComponent } from 'vue';

const props = defineProps({
    service: {
        type: Object,
        required: true
    },
    categories: {
        type: Array,
        required: true
    }
});

// Tarih formatını düzenliyoruz - YYYY-MM-DD formatına dönüştürme
const formatDate = (dateString) => {
    if (!dateString) return '';
    
    // Eğer tarih zaten YYYY-MM-DD formatındaysa direkt döndür
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
        return dateString;
    }
    
    // Değilse, tarihi ayırıp yeniden formatlıyoruz
    const date = new Date(dateString);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    
    return `${year}-${month}-${day}`;
};

// Kategori ağacını oluştur
const categoryTree = computed(() => {
    // Ana kategorileri (parent_id = null) bul ve isme göre sırala
    const rootCategories = props.categories
        .filter(c => c.parent_id === null)
        .sort((a, b) => a.name.localeCompare(b.name));
    
    // Recursive olarak child kategorileri ekle ve her seviyede isme göre sırala
    const buildTree = (categories) => {
        return categories.map(category => {
            const children = props.categories
                .filter(c => c.parent_id === category.id)
                .sort((a, b) => a.name.localeCompare(b.name));
                
            return {
                ...category,
                children: children.length ? buildTree(children) : []
            };
        });
    };
    
    return buildTree(rootCategories);
});

// Mevcut servis işlemlerini form için hazırla
const initialItems = props.service.items.map(item => ({
    id: item.id,
    category_id: item.category_id,
    completed: item.completed,
    notes: item.notes || '',
    part_cost: item.part_cost || ''
}));

const form = useForm({
    mileage: props.service.mileage,
    service_date: formatDate(props.service.service_date),
    labor_cost: props.service.labor_cost || '',
    items: initialItems
});

// Başlangıçta seçili olan kategorileri belirle
const selectedCategories = ref(initialItems.map(item => item.category_id));

// Modal state
const isModalOpen = ref(false);
const openModal = () => {
    isModalOpen.value = true;
    // Seçili kategorilerin üst kategorilerini otomatik aç
    expandSelectedCategoryParents();
};
const closeModal = () => {
    isModalOpen.value = false;
};

// Kategorinin alt kategorisi var mı kontrol et
const hasChildren = (categoryId) => {
    return props.categories.some(c => c.parent_id === categoryId);
};

// Collapse/expand for category sections
const expandedCategories = ref(new Set());
const toggleCategorySection = (categoryId) => {
    if (expandedCategories.value.has(categoryId)) {
        expandedCategories.value.delete(categoryId);
    } else {
        expandedCategories.value.add(categoryId);
    }
};
const isCategoryExpanded = (categoryId) => {
    return expandedCategories.value.has(categoryId);
};

// Search functionality
const searchQuery = ref('');
const filteredCategoryTree = computed(() => {
    if (!searchQuery.value.trim()) {
        return categoryTree.value;
    }
    
    const query = searchQuery.value.toLowerCase().trim();
    const matchesSearch = (category) => {
        return category.name.toLowerCase().includes(query);
    };
    
    // Filter the tree recursively
    const filterTree = (categories) => {
        return categories
            .map(category => {
                // Filter children recursively
                const filteredChildren = category.children.length 
                    ? filterTree(category.children) 
                    : [];
                
                // Include this category if it matches or has matching children
                if (matchesSearch(category) || filteredChildren.length > 0) {
                    return {
                        ...category,
                        children: filteredChildren
                    };
                }
                
                return null;
            })
            .filter(Boolean); // Remove null entries
    };
    
    return filterTree(categoryTree.value);
});

// Find parent category id for a given category
const findParentCategoryId = (categoryId) => {
    const category = props.categories.find(c => c.id === categoryId);
    return category ? category.parent_id : null;
};

// Expand all parent categories of selected items
const expandSelectedCategoryParents = () => {
    selectedCategories.value.forEach(categoryId => {
        let parentId = findParentCategoryId(categoryId);
        while (parentId) {
            expandedCategories.value.add(parentId);
            parentId = findParentCategoryId(parentId);
        }
    });
};

// Kategori seçme işlemi
const toggleCategory = (categoryId) => {
    // Eğer kategorinin alt kategorisi varsa, seçilemez
    if (hasChildren(categoryId)) {
        return;
    }
    
    const index = selectedCategories.value.indexOf(categoryId);
    if (index === -1) {
        selectedCategories.value.push(categoryId);
    } else {
        selectedCategories.value.splice(index, 1);
    }
};

// Seçili kategorileri servis işlemlerine dönüştür
const addSelectedCategories = () => {
    // Mevcut işlemler kategori ID'lerini alalım
    let existingCategoryIds = form.items.map(item => item.category_id);
    
    // Seçilen her kategori için işlem ekle
    selectedCategories.value.forEach(categoryId => {
        // Eğer bu kategori zaten eklenmediyse
        if (!existingCategoryIds.includes(categoryId)) {
            form.items.push({
                category_id: categoryId,
                completed: false,
                notes: '',
                part_cost: ''
            });
        }
    });
    closeModal();
};

// Servis işlemini kaldır
const removeServiceItem = (index) => {
    // İşlemi kaldırırken seçili kategorilerden de çıkar
    const categoryId = form.items[index].category_id;
    const catIndex = selectedCategories.value.indexOf(categoryId);
    if (catIndex !== -1) {
        selectedCategories.value.splice(catIndex, 1);
    }
    form.items.splice(index, 1);
};

// Seçili kategorinin adını bul
const getCategoryName = (categoryId) => {
    const category = props.categories.find(c => c.id === categoryId);
    return category ? category.name : '';
};

// Form gönderme
const submit = () => {
    form.patch(route('services.update', props.service.id));
};
</script>

<template>
    <Head title="Servis Düzenle" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Servis Düzenle - {{ service.vehicle.plate_no }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Kilometre ve Tarih Bilgileri -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <InputLabel for="mileage" value="Kilometre" class="text-gray-900 dark:text-gray-100" />
                                    <TextInput
                                        id="mileage"
                                        type="number"
                                        class="mt-1 block w-full dark:bg-gray-900 dark:text-gray-100"
                                        v-model="form.mileage"
                                        required
                                    />
                                    <InputError :message="form.errors.mileage" class="mt-2" />
                                </div>
                                <div>
                                    <InputLabel for="service_date" value="Servis Tarihi" class="text-gray-900 dark:text-gray-100" />
                                    <TextInput
                                        id="service_date"
                                        type="date"
                                        class="mt-1 block w-full dark:bg-gray-900 dark:text-gray-100"
                                        v-model="form.service_date"
                                        required
                                    />
                                    <InputError :message="form.errors.service_date" class="mt-2" />
                                </div>
                                <div>
                                    <InputLabel for="labor_cost" value="İşçilik Ücreti (TL)" class="text-gray-900 dark:text-gray-100" />
                                    <TextInput
                                        id="labor_cost"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full dark:bg-gray-900 dark:text-gray-100"
                                        v-model="form.labor_cost"
                                        placeholder="0.00"
                                    />
                                    <InputError :message="form.errors.labor_cost" class="mt-2" />
                                </div>
                            </div>

                            <!-- Servis İşlemleri Bölümü -->
                            <div class="space-y-4">
                                <div class="flex justify-between items-center">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Servis İşlemleri</h3>
                                    <button
                                        type="button"
                                        @click="openModal"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-600 focus:bg-green-600 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        İşlem Seç
                                    </button>
                                </div>

                                <!-- Seçili İşlemler Listesi -->
                                <div v-if="form.items.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-8">
                                    Henüz servis işlemi seçilmedi. Yukarıdaki "İşlem Ekle/Çıkar" düğmesine tıklayarak işlem ekleyebilirsiniz.
                                </div>
                                
                                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div v-for="(item, index) in form.items" :key="index" 
                                         class="border border-gray-200 dark:border-gray-700 p-4 rounded-md">
                                        <div class="flex justify-between items-start mb-3">
                                            <h4 class="font-medium text-gray-900 dark:text-gray-100">

                                                <label class="inline-flex items-center">
                                                    <input
                                                        type="checkbox"
                                                        class="rounded border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600"
                                                        v-model="item.completed"
                                                    >
                                                    <span class="ml-2">{{ getCategoryName(item.category_id) }}</span>
                                                </label>                                            
                                            </h4>
                                            <button
                                                type="button"
                                                @click="removeServiceItem(index)"
                                                class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 gap-3">
                                            <textarea
                                                v-model="item.notes"
                                                placeholder="Notlar"
                                                rows="2"
                                                class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                            ></textarea>
                                            <InputError :message="form.errors['items.' + index + '.notes']" class="mt-1" />
                                            
                                            <div>
                                                <InputLabel :for="'part_cost_' + index" value="Parça Ücreti (TL)" class="text-gray-900 dark:text-gray-100" />
                                                <TextInput
                                                    :id="'part_cost_' + index"
                                                    type="number"
                                                    step="0.01"
                                                    class="mt-1 block w-full dark:bg-gray-900 dark:text-gray-100"
                                                    v-model="item.part_cost"
                                                    placeholder="0.00"
                                                />
                                                <InputError :message="form.errors['items.' + index + '.part_cost']" class="mt-1" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Genel Hata Mesajı -->
                                <InputError :message="form.errors.items" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton class="ml-4" :disabled="form.processing">
                                    Güncelle
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kategori Seçim Modalı -->
        <div v-if="isModalOpen" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full max-h-[80vh] flex flex-col">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">İşlem Kategorileri</h3>
                        <button 
                            @click="closeModal"
                            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            v-model="searchQuery"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                            placeholder="Kategorilerde ara..."
                        />
                    </div>
                </div>
                
                <div class="p-4 overflow-y-auto flex-grow">
                    <div class="space-y-1">
                        <!-- Filtrelenmiş kategorileri göster -->
                        <div v-for="category in filteredCategoryTree" :key="category.id">
                            <!-- Ana kategori -->
                            <div class="py-2 flex items-center">
                                <button 
                                    v-if="category.children && category.children.length" 
                                    @click="toggleCategorySection(category.id)" 
                                    class="mr-2 w-5 h-5 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none transform transition-transform duration-200"
                                    :class="{'rotate-90': isCategoryExpanded(category.id)}"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div v-else class="w-5 h-5 mr-2"></div>
                                
                                <input 
                                    type="checkbox" 
                                    :id="'cat-' + category.id" 
                                    :checked="selectedCategories.includes(category.id)"
                                    @change="toggleCategory(category.id)"
                                    :disabled="hasChildren(category.id)"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    :class="{'opacity-50 cursor-not-allowed': hasChildren(category.id)}"
                                >
                                <label 
                                    :for="'cat-' + category.id" 
                                    class="ml-2 cursor-pointer"
                                    :class="{
                                        'text-gray-900 dark:text-gray-100': !hasChildren(category.id),
                                        'text-gray-500 dark:text-gray-400 font-semibold': hasChildren(category.id)
                                    }"
                                    @click="category.children && category.children.length ? toggleCategorySection(category.id) : null"
                                >
                                    {{ category.name }}
                                    <span v-if="hasChildren(category.id)" class="text-xs">(Seçilemez)</span>
                                </label>
                            </div>
                            
                            <!-- Alt kategoriler (collapse edilebilir) -->
                            <div v-if="category.children && category.children.length && (isCategoryExpanded(category.id) || searchQuery)">
                                <div v-for="child in category.children" :key="child.id">
                                    <div class="py-2 flex items-center" style="padding-left: 20px;">
                                        <button 
                                            v-if="child.children && child.children.length" 
                                            @click="toggleCategorySection(child.id)" 
                                            class="mr-2 w-5 h-5 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none transform transition-transform duration-200"
                                            :class="{'rotate-90': isCategoryExpanded(child.id)}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                        <div v-else class="w-5 h-5 mr-2"></div>
                                        
                                        <input 
                                            type="checkbox" 
                                            :id="'cat-' + child.id" 
                                            :checked="selectedCategories.includes(child.id)"
                                            @change="toggleCategory(child.id)"
                                            :disabled="hasChildren(child.id)"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                            :class="{'opacity-50 cursor-not-allowed': hasChildren(child.id)}"
                                        >
                                        <label 
                                            :for="'cat-' + child.id" 
                                            class="ml-2 cursor-pointer"
                                            :class="{
                                                'text-gray-900 dark:text-gray-100': !hasChildren(child.id),
                                                'text-gray-500 dark:text-gray-400 font-semibold': hasChildren(child.id)
                                            }"
                                            @click="child.children && child.children.length ? toggleCategorySection(child.id) : null"
                                        >
                                            {{ child.name }}
                                            <span v-if="hasChildren(child.id)" class="text-xs">(Seçilemez)</span>
                                        </label>
                                    </div>
                                    
                                    <!-- İkinci seviye alt kategoriler -->
                                    <div v-if="child.children && child.children.length && (isCategoryExpanded(child.id) || searchQuery)">
                                        <div v-for="subchild in child.children" :key="subchild.id">
                                            <div class="py-2 flex items-center" style="padding-left: 40px;">
                                                <div class="w-5 h-5 mr-2"></div>
                                                <input 
                                                    type="checkbox" 
                                                    :id="'cat-' + subchild.id" 
                                                    :checked="selectedCategories.includes(subchild.id)"
                                                    @change="toggleCategory(subchild.id)"
                                                    :disabled="hasChildren(subchild.id)"
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                    :class="{'opacity-50 cursor-not-allowed': hasChildren(subchild.id)}"
                                                >
                                                <label 
                                                    :for="'cat-' + subchild.id" 
                                                    class="ml-2 cursor-pointer"
                                                    :class="{
                                                        'text-gray-900 dark:text-gray-100': !hasChildren(subchild.id),
                                                        'text-gray-500 dark:text-gray-400 font-semibold': hasChildren(subchild.id)
                                                    }"
                                                >
                                                    {{ subchild.name }}
                                                    <span v-if="hasChildren(subchild.id)" class="text-xs">(Seçilemez)</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sonuç bulunamadı mesajı -->
                        <div v-if="searchQuery && filteredCategoryTree.length === 0" class="text-center py-4 text-gray-500 dark:text-gray-400">
                            Aranan kriterlere uygun kategori bulunamadı.
                        </div>
                    </div>
                </div>
                
                <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                    <button 
                        @click="closeModal"
                        class="px-4 py-2 mr-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400"
                    >
                        İptal
                    </button>
                    <button 
                        @click="addSelectedCategories"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        Kategorileri Uygula
                    </button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>