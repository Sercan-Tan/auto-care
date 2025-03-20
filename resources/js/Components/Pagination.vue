<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true
    }
});
</script>

<template>
    <div v-if="links.length > 3">
        <div class="flex flex-wrap -mb-1">
            <template v-for="(link, key) in links" :key="key">
                <div v-if="link.url === null" class="mr-1 mb-1 px-4 py-2 text-sm leading-4 text-gray-400 dark:text-gray-600 border border-gray-300 dark:border-gray-700 rounded"
                    v-html="link.label" />
                <Link v-else
                    class="mr-1 mb-1 px-4 py-2 text-sm leading-4 border border-gray-300 dark:border-gray-700 rounded hover:bg-gray-100 dark:hover:bg-gray-700 focus:border-indigo-500 focus:text-indigo-500 dark:focus:text-indigo-400"
                    :class="{ 'bg-blue-700 dark:bg-blue-600 text-white': link.active }" :href="link.url" v-html="link.label" />
            </template>
        </div>
    </div>
</template>