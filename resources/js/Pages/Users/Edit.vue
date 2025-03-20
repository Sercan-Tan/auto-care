<template>
    <Head title="Kullanıcı Düzenle" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Kullanıcı Düzenle</h2>
                <Link :href="route('users.index')" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Geri Dön
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <form @submit.prevent="submit">
                            <div class="mb-4">
                                <InputLabel for="name" value="Adı" />
                                <TextInput
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full"
                                    autofocus
                                    autocomplete="name"
                                />
                                <InputError :message="form.errors.name" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="email" value="E-posta" />
                                <TextInput
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full"
                                    autocomplete="email"
                                />
                                <InputError :message="form.errors.email" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="password" value="Şifre (Değiştirmek istemiyorsanız boş bırakın)" />
                                <TextInput
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.password" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="password_confirmation" value="Şifre Tekrarı" />
                                <TextInput
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.password_confirmation" class="mt-2" />
                            </div>

                            <div class="mb-4">
                                <InputLabel for="role" value="Rol" />
                                <select
                                    id="role"
                                    v-model="form.role"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                                >
                                    <option value="admin">Yönetici</option>
                                    <option value="service">Servis</option>
                                </select>
                                <InputError :message="form.errors.role" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton :disabled="form.processing" class="ml-4">
                                    Güncelle
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    user: Object
});

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    role: props.user.role,
});

const submit = () => {
    form.put(route('users.update', props.user.id));
};
</script>