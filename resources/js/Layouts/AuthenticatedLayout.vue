<script setup lang="ts">
import { ref, computed } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

interface PageProps {
    flash: {
        message: string | null;
    };
}

const showingNavigationDropdown = ref(false);
const showFlash = ref(true);

// Flash mesajı için computed property
const flash = computed(() => (usePage().props.flash || { message: null }) as PageProps['flash']);

// Flash mesajını 5 saniye sonra otomatik kapat
if (flash.value?.message) {
    setTimeout(() => {
        showFlash.value = false;
    }, 5000);
}

const closeFlash = () => {
    showFlash.value = false;
};
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
            <!-- Flash Message -->
            <div v-if="flash?.message && showFlash" 
                 class="fixed top-4 right-4 z-50 flex items-center rounded-lg border px-4 py-3 shadow-lg bg-green-50 dark:bg-green-900/50 border-green-400 dark:border-green-800 text-green-700 dark:text-green-200"
                 role="alert"
            >
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="block font-medium sm:inline">{{ flash.message }}</span>
                </div>
                <button @click="closeFlash" class="ml-4 text-green-700 dark:text-green-200 hover:text-green-900 dark:hover:text-green-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <nav
                class="border-b border-gray-100 bg-white dark:border-gray-700 dark:bg-gray-800"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10V14.5C4 15.9045 4 16.6067 4.33706 17.1111C4.48298 17.3295 4.67048 17.517 4.88886 17.6629C5.39331 18 6.09554 18 7.5 18H12.5C13.9045 18 14.6067 18 15.1111 17.6629C15.3295 17.517 15.517 17.3295 15.6629 17.1111C16 16.6067 16 15.9045 16 14.5V10"></path><path d="M12 18V13.875C12 13.5239 12 13.3483 11.9157 13.2222C11.8793 13.1676 11.8324 13.1207 11.7778 13.0843C11.6517 13 11.4761 13 11.125 13H8.875C8.52388 13 8.34833 13 8.22221 13.0843C8.16762 13.1207 8.12074 13.1676 8.08427 13.2222C8 13.3483 8 13.5239 8 13.875V18"></path><path d="M2 9.5L3.30715 4.59818C3.59125 3.53283 3.73329 3.00016 4.07581 2.63391C4.22529 2.47406 4.4 2.33984 4.59297 2.23658C5.03511 2 5.5864 2 6.68897 2H13.311C14.4136 2 14.9649 2 15.407 2.23658C15.6 2.33984 15.7747 2.47406 15.9242 2.63391C16.2667 3.00016 16.4088 3.53283 16.6928 4.59818L18 9.5"></path><path d="M2 9.5H5.39023C6.0676 9.5 6.40629 9.5 6.71555 9.39638C6.85193 9.35068 6.98299 9.2904 7.10644 9.21659C7.38638 9.04922 7.6068 8.79207 8.04763 8.27777L9.33565 6.77507C9.59815 6.46882 9.7294 6.3157 9.89306 6.27987C9.96352 6.26444 10.0365 6.26444 10.1069 6.27987C10.2706 6.3157 10.4018 6.46882 10.6643 6.77507L11.9524 8.27777C12.3932 8.79207 12.6136 9.04922 12.8936 9.21659C13.017 9.2904 13.1481 9.35068 13.2844 9.39638C13.5937 9.5 13.9324 9.5 14.6098 9.5H18"></path></svg>
                                        Gösterge Paneli
                                    </div>
                                </NavLink>
                                <NavLink
                                    :href="route('vehicles.index')"
                                    :active="route().current('vehicles.*')"
                                >
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg"class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12C3 11.0681 3 10.6022 3.15224 10.2346 3.35523 9.74458 3.74458 9.35523 4.23463 9.15224 4.60218 9 5.06812 9 6 9H14C14.9319 9 15.3978 9 15.7654 9.15224 16.2554 9.35523 16.6448 9.74458 16.8478 10.2346 17 10.6022 17 11.0681 17 12V12C17 12.9319 17 13.3978 16.8478 13.7654 16.6448 14.2554 16.2554 14.6448 15.7654 14.8478 15.3978 15 14.9319 15 14 15H6C5.06812 15 4.60218 15 4.23463 14.8478 3.74458 14.6448 3.35523 14.2554 3.15224 13.7654 3 13.3978 3 12.9319 3 12V12zM5.09262 5.17584C5.29047 4.48337 5.38939 4.13713 5.57383 3.86837 5.81974 3.51001 6.1757 3.24151 6.58783 3.1035 6.89692 3 7.25701 3 7.97719 3H12.0228C12.743 3 13.1031 3 13.4122 3.1035 13.8243 3.24151 14.1803 3.51001 14.4262 3.86837 14.6106 4.13713 14.7095 4.48337 14.9074 5.17584V5.17584C15.2364 6.32737 15.4009 6.90313 15.3148 7.36588 15.2 7.98287 14.8022 8.51019 14.2405 8.79008 13.8192 9 13.2204 9 12.0228 9H7.97719C6.77958 9 6.18077 9 5.75949 8.79008 5.19778 8.51019 4.80002 7.98287 4.68521 7.36588 4.5991 6.90313 4.76361 6.32737 5.09262 5.17584V5.17584z"></path><path opacity="0.9" d="M13 12 13.5 12 14 12M16 8 16.5 8 17 8M3 8 3.5 8 4 8M6 12 6.5 12 7 12"></path><path d="M6 15C6.55228 15 7 15.4477 7 16L7 17C7 17.5523 6.55228 18 6 18V18C5.44772 18 5 17.5523 5 17L5 16C5 15.4477 5.44772 15 6 15V15zM14 15C14.5523 15 15 15.4477 15 16L15 17C15 17.5523 14.5523 18 14 18V18C13.4477 18 13 17.5523 13 17L13 16C13 15.4477 13.4477 15 14 15V15z"></path></svg>
                                        Araçlar
                                    </div>
                                </NavLink>
                                <NavLink
                                    :href="route('services.index')"
                                    :active="route().current('services.*')"
                                >
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8.32233 3.75427C8.52487 1.45662 11.776 1.3967 11.898 3.68836C11.9675 4.99415 13.2898 5.76859 14.4394 5.17678C16.4568 4.13815 18.0312 7.02423 16.1709 8.35098C15.111 9.10697 15.0829 10.7051 16.1171 11.4225C17.932 12.6815 16.2552 15.6275 14.273 14.6626C13.1434 14.1128 11.7931 14.9365 11.6777 16.2457C11.4751 18.5434 8.22404 18.6033 8.10202 16.3116C8.03249 15.0059 6.71017 14.2314 5.56062 14.8232C3.54318 15.8619 1.96879 12.9758 3.82906 11.649C4.88905 10.893 4.91709 9.29487 3.88295 8.57749C2.06805 7.31848 3.74476 4.37247 5.72705 5.33737C6.85656 5.88718 8.20692 5.06347 8.32233 3.75427Z"></path><path d="M10 8C11.1046 8 12 8.89543 12 10V10C12 11.1046 11.1046 12 10 12V12C8.89543 12 8 11.1046 8 10V10C8 8.89543 8.89543 8 10 8V8Z"></path></svg>                                    
                                        Servisler
                                    </div>
                                </NavLink>
                                <NavLink
                                    v-if="$page.props.auth.user.role === 'admin'"
                                    :href="route('costs.index')"
                                    :active="route().current('costs.*')"
                                >
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="8"></circle><path d="M8.2474 12.7882C9.21854 13.0238 10.3165 13.1347 11.255 12.7328C11.8747 12.4349 12.0341 11.9499 11.9943 11.3645C11.9508 10.4361 10.8492 10.0759 10.023 9.97886C9.37073 9.86455 8.53367 9.66016 8.15681 9.09552C7.83068 8.47891 8.00824 7.59903 8.73659 7.25954C9.4 6.95033 10.8166 6.87849 11.6536 7.25954"></path><path d="M10 5.5V7M10 13V14.5"></path></svg>                                                                  
                                        Maliyet
                                    </div>
                                </NavLink>                            
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <!-- Ayarlar Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                                            >
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8.32233 3.75427C8.52487 1.45662 11.776 1.3967 11.898 3.68836C11.9675 4.99415 13.2898 5.76859 14.4394 5.17678C16.4568 4.13815 18.0312 7.02423 16.1709 8.35098C15.111 9.10697 15.0829 10.7051 16.1171 11.4225C17.932 12.6815 16.2552 15.6275 14.273 14.6626C13.1434 14.1128 11.7931 14.9365 11.6777 16.2457C11.4751 18.5434 8.22404 18.6033 8.10202 16.3116C8.03249 15.0059 6.71017 14.2314 5.56062 14.8232C3.54318 15.8619 1.96879 12.9758 3.82906 11.649C4.88905 10.893 4.91709 9.29487 3.88295 8.57749C2.06805 7.31848 3.74476 4.37247 5.72705 5.33737C6.85656 5.88718 8.20692 5.06347 8.32233 3.75427Z"></path><path d="M10 8C11.1046 8 12 8.89543 12 10V10C12 11.1046 11.1046 12 10 12V12C8.89543 12 8 11.1046 8 10V10C8 8.89543 8.89543 8 10 8V8Z"></path></svg>
                                                Ayarlar
                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('categories.index')"
                                            :active="route().current('categories.*')"
                                        >
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                                                </svg>
                                                Kategoriler
                                            </div>
                                        </DropdownLink>

                                        <DropdownLink v-if="$page.props.auth.user.role === 'admin'" :href="route('users.index')">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                                </svg>
                                                Kullanıcılar
                                            </div>
                                        </DropdownLink>
                                        <DropdownLink :href="route('backups.index')">
                                            <div class="flex items-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16 10.3536 17.6464C10.1583 17.8417 9.84171 17.8417 9.64645 17.6464L8 16M10 7 10 17"></path><path d="M15 13C16.5 13 18 12 18 8.94737C18 6.69591 16.2636 4.89474 14.093 4.89474C14.031 4.89474 13.969 4.89474 13.907 4.89474C13.2248 3.22222 11.6124 2 9.68992 2C7.33333 2.06433 5.41085 3.92983 5.22481 6.30994C3.42636 6.30994 2 7.78947 2 9.65497C2 11.5205 3.42636 13 5.22481 13"></path></svg>
                                                Yedekleme
                                            </div>
                                        </DropdownLink>                                    
                                    </template>
                                </Dropdown>
                            </div>
                            
                            <!-- Kullanıcı Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none dark:bg-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profil
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Çıkış Yap
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none dark:text-gray-500 dark:hover:bg-gray-900 dark:hover:text-gray-400 dark:focus:bg-gray-900 dark:focus:text-gray-400"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 10V14.5C4 15.9045 4 16.6067 4.33706 17.1111C4.48298 17.3295 4.67048 17.517 4.88886 17.6629C5.39331 18 6.09554 18 7.5 18H12.5C13.9045 18 14.6067 18 15.1111 17.6629C15.3295 17.517 15.517 17.3295 15.6629 17.1111C16 16.6067 16 15.9045 16 14.5V10"></path><path d="M12 18V13.875C12 13.5239 12 13.3483 11.9157 13.2222C11.8793 13.1676 11.8324 13.1207 11.7778 13.0843C11.6517 13 11.4761 13 11.125 13H8.875C8.52388 13 8.34833 13 8.22221 13.0843C8.16762 13.1207 8.12074 13.1676 8.08427 13.2222C8 13.3483 8 13.5239 8 13.875V18"></path><path d="M2 9.5L3.30715 4.59818C3.59125 3.53283 3.73329 3.00016 4.07581 2.63391C4.22529 2.47406 4.4 2.33984 4.59297 2.23658C5.03511 2 5.5864 2 6.68897 2H13.311C14.4136 2 14.9649 2 15.407 2.23658C15.6 2.33984 15.7747 2.47406 15.9242 2.63391C16.2667 3.00016 16.4088 3.53283 16.6928 4.59818L18 9.5"></path><path d="M2 9.5H5.39023C6.0676 9.5 6.40629 9.5 6.71555 9.39638C6.85193 9.35068 6.98299 9.2904 7.10644 9.21659C7.38638 9.04922 7.6068 8.79207 8.04763 8.27777L9.33565 6.77507C9.59815 6.46882 9.7294 6.3157 9.89306 6.27987C9.96352 6.26444 10.0365 6.26444 10.1069 6.27987C10.2706 6.3157 10.4018 6.46882 10.6643 6.77507L11.9524 8.27777C12.3932 8.79207 12.6136 9.04922 12.8936 9.21659C13.017 9.2904 13.1481 9.35068 13.2844 9.39638C13.5937 9.5 13.9324 9.5 14.6098 9.5H18"></path></svg>
                                Gösterge Paneli
                            </div>
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('vehicles.index')"
                            :active="route().current('vehicles.*')"
                        >
                            <div class="flex items-center">                                
                                <svg xmlns="http://www.w3.org/2000/svg"class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12C3 11.0681 3 10.6022 3.15224 10.2346 3.35523 9.74458 3.74458 9.35523 4.23463 9.15224 4.60218 9 5.06812 9 6 9H14C14.9319 9 15.3978 9 15.7654 9.15224 16.2554 9.35523 16.6448 9.74458 16.8478 10.2346 17 10.6022 17 11.0681 17 12V12C17 12.9319 17 13.3978 16.8478 13.7654 16.6448 14.2554 16.2554 14.6448 15.7654 14.8478 15.3978 15 14.9319 15 14 15H6C5.06812 15 4.60218 15 4.23463 14.8478 3.74458 14.6448 3.35523 14.2554 3.15224 13.7654 3 13.3978 3 12.9319 3 12V12zM5.09262 5.17584C5.29047 4.48337 5.38939 4.13713 5.57383 3.86837 5.81974 3.51001 6.1757 3.24151 6.58783 3.1035 6.89692 3 7.25701 3 7.97719 3H12.0228C12.743 3 13.1031 3 13.4122 3.1035 13.8243 3.24151 14.1803 3.51001 14.4262 3.86837 14.6106 4.13713 14.7095 4.48337 14.9074 5.17584V5.17584C15.2364 6.32737 15.4009 6.90313 15.3148 7.36588 15.2 7.98287 14.8022 8.51019 14.2405 8.79008 13.8192 9 13.2204 9 12.0228 9H7.97719C6.77958 9 6.18077 9 5.75949 8.79008 5.19778 8.51019 4.80002 7.98287 4.68521 7.36588 4.5991 6.90313 4.76361 6.32737 5.09262 5.17584V5.17584z"></path><path opacity="0.9" d="M13 12 13.5 12 14 12M16 8 16.5 8 17 8M3 8 3.5 8 4 8M6 12 6.5 12 7 12"></path><path d="M6 15C6.55228 15 7 15.4477 7 16L7 17C7 17.5523 6.55228 18 6 18V18C5.44772 18 5 17.5523 5 17L5 16C5 15.4477 5.44772 15 6 15V15zM14 15C14.5523 15 15 15.4477 15 16L15 17C15 17.5523 14.5523 18 14 18V18C13.4477 18 13 17.5523 13 17L13 16C13 15.4477 13.4477 15 14 15V15z"></path></svg>
                                Araçlar
                            </div>
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('services.index')"
                            :active="route().current('services.*')"
                        >
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M8.32233 3.75427C8.52487 1.45662 11.776 1.3967 11.898 3.68836C11.9675 4.99415 13.2898 5.76859 14.4394 5.17678C16.4568 4.13815 18.0312 7.02423 16.1709 8.35098C15.111 9.10697 15.0829 10.7051 16.1171 11.4225C17.932 12.6815 16.2552 15.6275 14.273 14.6626C13.1434 14.1128 11.7931 14.9365 11.6777 16.2457C11.4751 18.5434 8.22404 18.6033 8.10202 16.3116C8.03249 15.0059 6.71017 14.2314 5.56062 14.8232C3.54318 15.8619 1.96879 12.9758 3.82906 11.649C4.88905 10.893 4.91709 9.29487 3.88295 8.57749C2.06805 7.31848 3.74476 4.37247 5.72705 5.33737C6.85656 5.88718 8.20692 5.06347 8.32233 3.75427Z"></path><path d="M10 8C11.1046 8 12 8.89543 12 10V10C12 11.1046 11.1046 12 10 12V12C8.89543 12 8 11.1046 8 10V10C8 8.89543 8.89543 8 10 8V8Z"></path></svg>                                    
                                Servisler
                            </div>
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="$page.props.auth.user.role === 'admin'"
                            :href="route('costs.index')"
                            :active="route().current('costs.*')"
                        >
                            <div class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 mr-1"><circle cx="10" cy="10" r="8"></circle><path d="M8.2474 12.7882C9.21854 13.0238 10.3165 13.1347 11.255 12.7328C11.8747 12.4349 12.0341 11.9499 11.9943 11.3645C11.9508 10.4361 10.8492 10.0759 10.023 9.97886C9.37073 9.86455 8.53367 9.66016 8.15681 9.09552C7.83068 8.47891 8.00824 7.59903 8.73659 7.25954C9.4 6.95033 10.8166 6.87849 11.6536 7.25954"></path><path d="M10 5.5V7M10 13V14.5"></path></svg>                                
                                Maliyet
                            </div>
                        </ResponsiveNavLink>
                                        
                        <!-- Ayarlar Menüsü Başlığı -->
                        <div class="mt-3 pt-1 border-t border-gray-200 dark:border-gray-600">
                            <div class="px-4 py-2">
                                <div class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Ayarlar
                                </div>
                            </div>
                    
                            <!-- Ayarlar Alt Menüleri -->

                            <ResponsiveNavLink
                                :href="route('categories.index')"
                                :active="route().current('categories.*')"
                            >
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                                    </svg>
                                    Kategoriler
                                </div>
                            </ResponsiveNavLink>

                            <ResponsiveNavLink
                                v-if="$page.props.auth.user.role === 'admin'"
                                :href="route('users.index')"
                                :active="route().current('users.*')"
                            >
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                    </svg>
                                    Kullanıcılar
                                </div>
                            </ResponsiveNavLink>
                            
                            <ResponsiveNavLink
                                :href="route('backups.index')"
                                :active="route().current('backups.*')"
                            >
                                <div class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16 10.3536 17.6464C10.1583 17.8417 9.84171 17.8417 9.64645 17.6464L8 16M10 7 10 17"></path><path d="M15 13C16.5 13 18 12 18 8.94737C18 6.69591 16.2636 4.89474 14.093 4.89474C14.031 4.89474 13.969 4.89474 13.907 4.89474C13.2248 3.22222 11.6124 2 9.68992 2C7.33333 2.06433 5.41085 3.92983 5.22481 6.30994C3.42636 6.30994 2 7.78947 2 9.65497C2 11.5205 3.42636 13 5.22481 13"></path></svg>
                                    Yedekleme
                                </div>
                            </ResponsiveNavLink>
                        </div>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4 dark:border-gray-600"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800 dark:text-gray-200"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Çıkış Yap
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white shadow dark:bg-gray-800"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>

<style>
@keyframes slideIn {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.fixed {
    animation: slideIn 0.3s ease-out;
}
</style>
