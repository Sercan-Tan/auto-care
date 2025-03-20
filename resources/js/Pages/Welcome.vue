<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    canLogin?: boolean;
    canRegister?: boolean;
    laravelVersion: string;
    phpVersion: string;
}>();
</script>

<template>
    <Head title="Araç Bakım Takip Sistemi" />
    <div class="bg-gray-50 dark:bg-gray-900 min-h-screen">
        <div class="relative flex flex-col items-center justify-center min-h-screen">
            <!-- Header ve Logo -->
            <div class="text-center mb-10">
                <div class="flex justify-center mb-6">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-[#FF2D20]" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12C3 11.0681 3 10.6022 3.15224 10.2346 3.35523 9.74458 3.74458 9.35523 4.23463 9.15224 4.60218 9 5.06812 9 6 9H14C14.9319 9 15.3978 9 15.7654 9.15224 16.2554 9.35523 16.6448 9.74458 16.8478 10.2346 17 10.6022 17 11.0681 17 12V12C17 12.9319 17 13.3978 16.8478 13.7654 16.6448 14.2554 16.2554 14.6448 15.7654 14.8478 15.3978 15 14.9319 15 14 15H6C5.06812 15 4.60218 15 4.23463 14.8478 3.74458 14.6448 3.35523 14.2554 3.15224 13.7654 3 13.3978 3 12.9319 3 12V12zM5.09262 5.17584C5.29047 4.48337 5.38939 4.13713 5.57383 3.86837 5.81974 3.51001 6.1757 3.24151 6.58783 3.1035 6.89692 3 7.25701 3 7.97719 3H12.0228C12.743 3 13.1031 3 13.4122 3.1035 13.8243 3.24151 14.1803 3.51001 14.4262 3.86837 14.6106 4.13713 14.7095 4.48337 14.9074 5.17584V5.17584C15.2364 6.32737 15.4009 6.90313 15.3148 7.36588 15.2 7.98287 14.8022 8.51019 14.2405 8.79008 13.8192 9 13.2204 9 12.0228 9H7.97719C6.77958 9 6.18077 9 5.75949 8.79008 5.19778 8.51019 4.80002 7.98287 4.68521 7.36588 4.5991 6.90313 4.76361 6.32737 5.09262 5.17584V5.17584z"></path><path opacity="0.9" d="M13 12 13.5 12 14 12M16 8 16.5 8 17 8M3 8 3.5 8 4 8M6 12 6.5 12 7 12"></path><path d="M6 15C6.55228 15 7 15.4477 7 16L7 17C7 17.5523 6.55228 18 6 18V18C5.44772 18 5 17.5523 5 17L5 16C5 15.4477 5.44772 15 6 15V15zM14 15C14.5523 15 15 15.4477 15 16L15 17C15 17.5523 14.5523 18 14 18V18C13.4477 18 13 17.5523 13 17L13 16C13 15.4477 13.4477 15 14 15V15z"></path></svg>
                </div>
                <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Araç Bakım Takip Sistemi
                </h1>
                <p class="text-xl text-gray-600 dark:text-gray-400">
                    Araç bakım süreçlerinizi kolayca yönetin
                </p>
            </div>

            <!-- Özellikler Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto px-4 mb-12">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <div class="flex items-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#FF2D20] mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M5 3a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2H5zM5 11a2 2 0 00-2 2v2a2 2 0 002 2h2a2 2 0 002-2v-2a2 2 0 00-2-2H5z" />
                            <path d="M11 5a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V5zM14 11a1 1 0 011 1v1h1a1 1 0 110 2h-1v1a1 1 0 11-2 0v-1h-1a1 1 0 110-2h1v-1a1 1 0 011-1z" />
                        </svg>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Detaylı Takip</h2>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">
                        Araçlarınızın bakım geçmişini ve gelecek bakım planlarını detaylı bir şekilde takip edin.
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <div class="flex items-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#FF2D20] mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                        </svg>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Bakım Planlaması</h2>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">
                        Bakım takvimini planlayın ve yaklaşan bakımlar hakkında bilgi sahibi olun.
                    </p>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg">
                    <div class="flex items-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-[#FF2D20] mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                        </svg>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Servis Yönetimi</h2>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400">
                        Servis işlemlerini kategorilere ayırın ve maliyetleri takip edin.
                    </p>
                </div>
            </div>

            <!-- Giriş Butonu -->
            <div class="text-center">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-[#FF2D20] hover:bg-[#FF2D20]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FF2D20]"
                >
                    Gösterge Paneline Git
                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </Link>
                <Link
                    v-else
                    :href="route('login')"
                    class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-[#FF2D20] hover:bg-[#FF2D20]/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#FF2D20]"
                >
                    Giriş Yap
                    <svg xmlns="http://www.w3.org/2000/svg" class="ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </Link>
            </div>
        </div>
    </div>
</template>
