<template>
  <!-- Spotlight Modal Overlay -->
  <div v-if="isVisible" class="fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-70 transition-opacity" @click="close">
    <div class="fixed top-0 inset-x-0 flex justify-center pt-[70px] px-4">
      <div style="animation: fadeInFromTop 0.35s cubic-bezier(0.21, 1.02, 0.73, 1);" class="bg-white dark:bg-gray-800 rounded-xl text-left overflow-hidden shadow-xl border border-gray-200 dark:border-gray-700 w-full max-w-2xl"
           role="dialog" 
           aria-modal="true" 
           aria-labelledby="modal-headline"
           @click.stop>
        
        <!-- Search Input -->
        <div class="p-4 sm:p-6 relative">
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <svg class="h-6 w-6 text-blue-500 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>

            <input
              ref="searchInput"
              type="text"
              v-model="searchQuery"
              @input="handleInput"
              @keydown.down.prevent="selectNextResult"
              @keydown.up.prevent="selectPreviousResult"
              @keydown.enter="goToSelectedResult"
              @keydown.esc="close"
              class="block w-full bg-white dark:bg-gray-800 border-0 text-lg py-3 pl-12 pr-10 text-gray-900 dark:text-gray-100 placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-0 sm:text-lg"
              placeholder="Araçlarım içinde ara..."
            />
            
            <!-- Clear search button -->
            <div v-if="searchQuery" class="absolute inset-y-0 right-0 pr-4 flex items-center">
              <button @click="clearSearch" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Keyboard Shortcut Hint -->
        <div v-if="!searchQuery" class="px-6 pt-0 pb-4 text-center text-sm text-gray-500 dark:text-gray-400">
          <div class="flex items-center justify-center space-x-3">
            <div class="flex items-center">
              <kbd class="px-2 py-1 mr-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded-lg dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600">↑</kbd>
              <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded-lg dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600">↓</kbd>
              <span class="ml-1">gezin,</span>
            </div>
            <div class="flex items-center">
              <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded-lg dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600">Enter</kbd>
              <span class="ml-1">seç,</span>
            </div>
            <div class="flex items-center">
              <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded-lg dark:bg-gray-700 dark:text-gray-100 dark:border-gray-600">ESC</kbd>
              <span class="ml-1">kapat</span>
            </div>
          </div>
        </div>

        <!-- Divider -->
        <div class="border-t border-gray-200 dark:border-gray-700"></div>

        <!-- Results List -->
        <div v-if="results.length > 0" class="max-h-96 overflow-y-auto">
          <ul class="py-1">
            <li 
              v-for="(result, index) in results" 
              :key="result.id"
              @click="selectResult(result)" 
              @mouseover="selectedIndex = index"
              :class="[
                'cursor-pointer px-6 py-3 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center transition-colors duration-150 ease-in-out',
                selectedIndex === index 
                  ? 'bg-blue-50 dark:bg-blue-900/40 border-l-4 border-blue-500 dark:border-blue-400' 
                  : 'border-l-4 border-transparent'
              ]"
            >
              <div class="flex items-center w-full">
                <div class="flex-shrink-0 mr-4">
                  <div class="flex items-center justify-center w-12 h-12 rounded-lg bg-gradient-to-br from-blue-100 to-blue-50 dark:from-blue-900/50 dark:to-indigo-900/50 shadow-sm text-blue-600 dark:text-blue-400">                    
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 17H16M8 17C8 18.1046 7.10457 19 6 19C4.89543 19 4 18.1046 4 17M8 17C8 15.8954 7.10457 15 6 15C4.89543 15 4 15.8954 4 17M16 17C16 18.1046 16.8954 19 18 19C19.1046 19 20 18.1046 20 17M16 17C16 15.8954 16.8954 15 18 15C19.1046 15 20 15.8954 20 17M10 5V11M4 11L4.33152 9.01088C4.56901 7.58593 4.68776 6.87345 5.0433 6.3388C5.35671 5.8675 5.79705 5.49447 6.31346 5.26281C6.8993 5 7.6216 5 9.06621 5H12.4311C13.3703 5 13.8399 5 14.2662 5.12945C14.6436 5.24406 14.9946 5.43194 15.2993 5.68236C15.6435 5.96523 15.904 6.35597 16.425 7.13744L19 11M4 17H3.6C3.03995 17 2.75992 17 2.54601 16.891C2.35785 16.7951 2.20487 16.6422 2.10899 16.454C2 16.2401 2 15.9601 2 15.4V14.2C2 13.0799 2 12.5198 2.21799 12.092C2.40973 11.7157 2.71569 11.4097 3.09202 11.218C3.51984 11 4.0799 11 5.2 11H17.2C17.9432 11 18.3148 11 18.6257 11.0492C20.3373 11.3203 21.6797 12.6627 21.9508 14.3743C22 14.6852 22 15.0568 22 15.8C22 15.9858 22 16.0787 21.9877 16.1564C21.9199 16.5843 21.5843 16.9199 21.1564 16.9877C21.0787 17 20.9858 17 20.8 17H20"/>
                    </svg>
                  </div>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="text-md font-semibold text-gray-900 dark:text-gray-100">{{ result.plate_no }}</div>
                  <div class="text-sm text-gray-600 dark:text-gray-400">{{ result.brand }} {{ result.model }} <span class="text-gray-500 dark:text-gray-500">•</span> {{ result.owner_name }}</div>
                </div>
                <div class="ml-4 opacity-70 group-hover:opacity-100 transition-opacity">
                  <div class="p-1 rounded-full bg-gray-100 dark:bg-gray-700">
                    <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                  </div>
                </div>
              </div>
            </li>
          </ul>
        </div>

        <!-- No Results -->
        <div v-else-if="searchQuery && !isLoading" class="px-6 py-10 text-center">
          <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
            <svg class="h-8 w-8 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Sonuç bulunamadı</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            "<span class="font-medium text-gray-700 dark:text-gray-300">{{ searchQuery }}</span>" ile eşleşen araç kaydı yok
          </p>
        </div>
        
        <!-- Loading State -->
        <div v-else-if="isLoading" class="px-6 py-10 text-center">
          <div class="inline-flex items-center justify-center mb-4">
            <svg class="animate-spin h-10 w-10 text-blue-500 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </div>
          <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-1">Araçlar aranıyor</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Lütfen bekleyin...
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, nextTick, defineProps, defineEmits } from 'vue';
import { debounce } from 'lodash';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  isVisible: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close']);

const searchInput = ref(null);
const searchQuery = ref('');
const results = ref([]);
const isLoading = ref(false);
const selectedIndex = ref(-1);

// Otomatik olarak input'a odaklan
watch(() => props.isVisible, async (visible) => {
  if (visible) {
    await nextTick();
    searchInput.value?.focus();
  } else {
    searchQuery.value = '';
    results.value = [];
  }
}, { immediate: true });

// Debounce the search to prevent too many API calls
const debouncedSearch = debounce(async (query) => {
  if (!query || query.length < 2) {
    results.value = [];
    isLoading.value = false;
    return;
  }

  try {
    isLoading.value = true;
    const response = await fetch(`/api/vehicles/search?query=${encodeURIComponent(query)}`);
    const data = await response.json();
    results.value = data;
    selectedIndex.value = data.length > 0 ? 0 : -1;
  } catch (error) {
    console.error('Search error:', error);
    results.value = [];
  } finally {
    isLoading.value = false;
  }
}, 300);

const handleInput = () => {
  debouncedSearch(searchQuery.value);
};

const selectNextResult = () => {
  if (results.value.length === 0) return;
  selectedIndex.value = (selectedIndex.value + 1) % results.value.length;
};

const selectPreviousResult = () => {
  if (results.value.length === 0) return;
  selectedIndex.value = selectedIndex.value <= 0 
    ? results.value.length - 1 
    : selectedIndex.value - 1;
};

const goToSelectedResult = () => {
  if (selectedIndex.value >= 0 && results.value.length > 0) {
    selectResult(results.value[selectedIndex.value]);
  } else {
    // Seçili öğe yoksa arama modalını kapat
    close();
  }
};

const selectResult = (result) => {
  searchQuery.value = '';
  results.value = [];
  close();
  router.visit(route('vehicles.show', result.id));
};

const clearSearch = () => {
  searchQuery.value = '';
  results.value = [];
  searchInput.value?.focus();
};

const close = () => {
  emit('close');
};

// Clear results when search is empty
watch(searchQuery, (newValue) => {
  if (!newValue) {
    results.value = [];
  }
});
</script>

<style scoped>
@keyframes fadeInFromTop {
  from { 
    opacity: 0;
    transform: translateY(-30px);
  }
  to { 
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
