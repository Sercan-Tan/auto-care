<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, computed } from 'vue';

// Araç markaları listesi
const carBrands = [
    'Acura', 'Alfa Romeo', 'Aston Martin', 'Audi', 'Bentley', 'BMW', 'Bugatti',
    'Buick', 'Cadillac', 'Chevrolet', 'Chrysler', 'Citroën', 'Dodge', 'Ferrari',
    'Fiat', 'Ford', 'Genesis', 'GMC', 'Honda', 'Hyundai', 'Infiniti', 'Jaguar',
    'Jeep', 'Kia', 'Lamborghini', 'Land Rover', 'Lexus', 'Lincoln', 'Lotus',
    'Maserati', 'Mazda', 'McLaren', 'Mercedes-Benz', 'MINI', 'Mitsubishi',
    'Nissan', 'Opel', 'Peugeot', 'Porsche', 'Ram', 'Renault', 'Rolls-Royce',
    'Saab', 'Subaru', 'Suzuki', 'Tesla', 'Toyota', 'Volkswagen', 'Volvo'
];

const props = defineProps({
    vehicle: {
        type: Object,
        required: true,
    },
});

const form = useForm({
    plate_no: props.vehicle.plate_no,
    brand: props.vehicle.brand,
    model: props.vehicle.model,
    year: props.vehicle.year,
    chassis_no: props.vehicle.chassis_no,
    owner_name: props.vehicle.owner_name,
    owner_phone: props.vehicle.owner_phone,
    owner_email: props.vehicle.owner_email,
});

const brandSearch = ref(props.vehicle.brand); // Başlangıç değerini props'tan al
const showBrandSuggestions = ref(false);
const selectedIndex = ref(-1); // Ok tuşları için seçili indeks

// Marka önerileri için computed property
const filteredBrands = computed(() => {
    if (!brandSearch.value) return [];
    const searchTerm = brandSearch.value.toLowerCase();
    return carBrands.filter(brand => 
        brand.toLowerCase().includes(searchTerm)
    ).slice(0, 5); // En fazla 5 öneri göster
});

const selectBrand = (brand) => {
    form.brand = brand;
    brandSearch.value = brand;
    showBrandSuggestions.value = false;
    selectedIndex.value = -1;
};

// Input değiştiğinde form.brand'i güncelle
const updateBrand = (event) => {
    form.brand = event.target.value;
    brandSearch.value = event.target.value;
    showBrandSuggestions.value = true;
};

const handleKeydown = (event) => {
    if (!showBrandSuggestions.value || filteredBrands.value.length === 0) return;

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            selectedIndex.value = (selectedIndex.value + 1) % filteredBrands.value.length;
            break;
        case 'ArrowUp':
            event.preventDefault();
            selectedIndex.value = selectedIndex.value <= 0 ? filteredBrands.value.length - 1 : selectedIndex.value - 1;
            break;
        case 'Enter':
            event.preventDefault();
            if (selectedIndex.value >= 0) {
                selectBrand(filteredBrands.value[selectedIndex.value]);
            }
            break;
        case 'Escape':
            showBrandSuggestions.value = false;
            selectedIndex.value = -1;
            break;
    }
};

const handleBlur = (event) => {
    // Tıklama için küçük bir gecikme ekleyelim
    setTimeout(() => {
        showBrandSuggestions.value = false;
        selectedIndex.value = -1;
    }, 200);
};

const submit = () => {
    form.put(route('vehicles.update', props.vehicle.id));
};

const formatPlate = (event) => {
    form.plate_no = event.target.value.replace(/\s+/g, '').toUpperCase();
};
</script>

<template>
    <Head title="Araç Düzenle" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Araç Düzenle</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6">
                        <div class="space-y-8">
                            <!-- Araç Bilgileri -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-3 mb-6">
                                    <div class="flex items-center">
                                        <svg class="w-6 h-6 mr-2 text-gray-500 dark:text-gray-400" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12C3 11.0681 3 10.6022 3.15224 10.2346 3.35523 9.74458 3.74458 9.35523 4.23463 9.15224 4.60218 9 5.06812 9 6 9H14C14.9319 9 15.3978 9 15.7654 9.15224 16.2554 9.35523 16.6448 9.74458 16.8478 10.2346 17 10.6022 17 11.0681 17 12V12C17 12.9319 17 13.3978 16.8478 13.7654 16.6448 14.2554 16.2554 14.6448 15.7654 14.8478 15.3978 15 14.9319 15 14 15H6C5.06812 15 4.60218 15 4.23463 14.8478 3.74458 14.6448 3.35523 14.2554 3.15224 13.7654 3 13.3978 3 12.9319 3 12V12zM5.09262 5.17584C5.29047 4.48337 5.38939 4.13713 5.57383 3.86837 5.81974 3.51001 6.1757 3.24151 6.58783 3.1035 6.89692 3 7.25701 3 7.97719 3H12.0228C12.743 3 13.1031 3 13.4122 3.1035 13.8243 3.24151 14.1803 3.51001 14.4262 3.86837 14.6106 4.13713 14.7095 4.48337 14.9074 5.17584V5.17584C15.2364 6.32737 15.4009 6.90313 15.3148 7.36588 15.2 7.98287 14.8022 8.51019 14.2405 8.79008 13.8192 9 13.2204 9 12.0228 9H7.97719C6.77958 9 6.18077 9 5.75949 8.79008 5.19778 8.51019 4.80002 7.98287 4.68521 7.36588 4.5991 6.90313 4.76361 6.32737 5.09262 5.17584V5.17584z"></path><path opacity="0.9" d="M13 12 13.5 12 14 12M16 8 16.5 8 17 8M3 8 3.5 8 4 8M6 12 6.5 12 7 12"></path><path d="M6 15C6.55228 15 7 15.4477 7 16L7 17C7 17.5523 6.55228 18 6 18V18C5.44772 18 5 17.5523 5 17L5 16C5 15.4477 5.44772 15 6 15V15zM14 15C14.5523 15 15 15.4477 15 16L15 17C15 17.5523 14.5523 18 14 18V18C13.4477 18 13 17.5523 13 17L13 16C13 15.4477 13.4477 15 14 15V15z"></path></svg>
                                        Araç Bilgileri
                                    </div>
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <InputLabel for="plate_no" value="Plaka Numarası" />
                                        <TextInput
                                            id="plate_no"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.plate_no"
                                            @input="formatPlate"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.plate_no" />
                                    </div>

                                    <div>
                                        <InputLabel for="chassis_no" value="Şasi Numarası" />
                                        <TextInput
                                            id="chassis_no"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.chassis_no"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.chassis_no" />
                                    </div>

                                    <div>
                                        <InputLabel for="brand" value="Marka" />
                                        <div class="relative">
                                            <TextInput
                                                id="brand"
                                                type="text"
                                                class="mt-1 block w-full"
                                                v-model="brandSearch"
                                                @input="updateBrand"
                                                @focus="showBrandSuggestions = true"
                                                @keydown="handleKeydown"
                                                @blur="handleBlur"
                                                autocomplete="off"
                                                required
                                            />
                                            <!-- Öneriler Listesi -->
                                            <div v-if="showBrandSuggestions && filteredBrands.length > 0" 
                                                class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg">
                                                <ul class="max-h-60 overflow-auto">
                                                    <li v-for="(brand, index) in filteredBrands" 
                                                        :key="brand" 
                                                        @click="selectBrand(brand)"
                                                        :class="[
                                                            'px-4 py-2 cursor-pointer text-gray-900 dark:text-gray-100',
                                                            index === selectedIndex ? 'bg-gray-100 dark:bg-gray-700' : 'hover:bg-gray-50 dark:hover:bg-gray-700'
                                                        ]">
                                                        {{ brand }}
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.brand" />
                                    </div>

                                    <div>
                                        <InputLabel for="model" value="Model" />
                                        <TextInput
                                            id="model"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.model"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.model" />
                                    </div>

                                    <div>
                                        <InputLabel for="year" value="Model Yılı" />
                                        <TextInput
                                            id="year"
                                            type="number"
                                            class="mt-1 block w-full"
                                            v-model="form.year"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.year" />
                                    </div>
                                </div>
                            </div>

                            <!-- Araç Sahibi Bilgileri -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b border-gray-200 dark:border-gray-700 pb-3 mb-6">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        Araç Sahibi Bilgileri
                                    </div>
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <InputLabel for="owner_name" value="Ad Soyad" />
                                        <TextInput
                                            id="owner_name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            v-model="form.owner_name"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.owner_name" />
                                    </div>

                                    <div>
                                        <InputLabel for="owner_phone" value="Telefon" />
                                        <TextInput
                                            id="owner_phone"
                                            type="tel"
                                            class="mt-1 block w-full"
                                            v-model="form.owner_phone"
                                            required
                                        />
                                        <InputError class="mt-2" :message="form.errors.owner_phone" />
                                    </div>

                                    <div class="md:col-span-2">
                                        <InputLabel for="owner_email" value="E-posta" />
                                        <TextInput
                                            id="owner_email"
                                            type="email"
                                            class="mt-1 block w-full"
                                            v-model="form.owner_email"
                                        />
                                        <InputError class="mt-2" :message="form.errors.owner_email" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Güncelle
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.relative {
    position: relative;
}
</style>