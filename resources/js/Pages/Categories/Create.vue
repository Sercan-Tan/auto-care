<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

// Controller'dan gelen hiyerarşik kategori yapısını düz (flat) listeye dönüştürme
const props = defineProps({
    categories: {
        type: Array,
        required: true
    }
});

// Kategorileri düz (flat) bir dizi haline getirme
const flattenCategories = (categories, result = []) => {
    categories.forEach(category => {
        result.push({
            id: category.id,
            name: category.name, // İsim zaten "— " ile hiyerarşiyi gösterecek şekilde formatlanmış durumda
            parent_id: category.parent_id
        });
        
        if (category.children && category.children.length > 0) {
            flattenCategories(category.children, result);
        }
    });
    
    return result;
};

// Düz listeye çevrilmiş kategoriler
const flattenedCategories = flattenCategories(props.categories);

const form = useForm({
    name: '',
    parent_id: ''
});

const submit = () => {
    form.post(route('categories.store'));
};
</script>

<template>
    <Head title="Yeni Kategori" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Yeni Kategori</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div>
                                <InputLabel for="name" value="Kategori Adı" class="text-gray-900 dark:text-gray-100" />
                                <TextInput
                                    id="name"
                                    type="text"
                                    class="mt-1 block w-full dark:bg-gray-900 dark:text-gray-100"
                                    v-model="form.name"
                                    required
                                />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div>
                                <InputLabel for="parent_id" value="Üst Kategori" class="text-gray-900 dark:text-gray-100" />
                                <select
                                    id="parent_id"
                                    v-model="form.parent_id"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                >
                                    <option value="">Ana Kategori</option>
                                    <option v-for="category in flattenedCategories" :key="category.id" :value="category.id">
                                        {{ category.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.parent_id" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton class="ml-4" :disabled="form.processing">
                                    Kaydet
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>