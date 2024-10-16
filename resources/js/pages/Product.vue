<script setup>
import { ref, onMounted, reactive, computed } from 'vue'
import { StarIcon } from '@heroicons/vue/20/solid'
import { RadioGroup, RadioGroupOption } from '@headlessui/vue'
import { useInventoryStore } from "../stores/Inventory.js";

const inventoryStore = useInventoryStore();
const props = defineProps({
    productId: {
        type: [String, Number],
        required: false
    }
});

const state = reactive({
    color: null,
    size: null,
});

onMounted(() => {
    inventoryStore.getProduct(props.productId);
});

function formatCurrency(value) {
    const numberValue = Number(value);
    if (isNaN(numberValue)) {
        return value; // Mantener el valor sin formatear si no es un número válido
    }
    return new Intl.NumberFormat('es-CO', {
        style: 'currency', currency: 'COP', minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(numberValue);
}

function changeImage(urlImage) {
    inventoryStore.selectedImage = urlImage;
}

const uniqueColors = computed(() => {
    // Verifica si `color_size` existe antes de intentar acceder a él
    if (inventoryStore.product && inventoryStore.product.color_size) {
        // Crear un Map para evitar duplicados basados en el color_name
        const colorMap = new Map();

        Object.entries(inventoryStore.product.color_size).forEach(([i, item]) => {
            if (!colorMap.has(item?.color_id)) {
                colorMap.set(item?.color_id, { color_id: item?.color_id, color: item.color, color_name: item.color_name });
            }
        });

        // Convertimos el Map a un array con los valores únicos
        const data = Array.from(colorMap.values());
        state.color = data[0];
        return data;
    }
    return [];
});

const uniqueSize = computed(() => {
    // Verifica si `color_size` existe antes de intentar acceder a él
    if (inventoryStore.product && inventoryStore.product.color_size) {
        // Crear un Map para evitar duplicados basados en el color_name
        const colorMap = new Map();

        Object.entries(inventoryStore?.product?.color_size).forEach(([key, item]) => {
            if (!colorMap.has(item?.size_id)) {
                if ((state.color?.color_id === item?.color_id || state.color === null)) {
                    colorMap.set(item?.size_id, {
                        size_id: item?.size_id,
                        size: item?.size,
                        size_name: item?.size_name,
                        active: (state?.color?.color_id === item?.color_id || state?.size?.size_id === item?.size_id)
                    });
                }
            }
        });

        // Convertimos el Map a un array con los valores únicos
        const data = Array.from(colorMap.values());
        // seleccionar la primera talla activa
        state.size = data.find(size => size.active);
        return data;
    }
    return [];
});
</script>

<template>
    <section class="bg-white antialiased grid justify-center" v-if="!inventoryStore.loadingProduct">
        <div class="max-w-screen-xl">
            <div class="lg:grid lg:grid-cols-2 lg:gap-8 xl:gap-16" v-if="inventoryStore.product">
                <div class="shrink-0 max-w-md lg:max-w-lg mx-auto">
                    <div class="grid gap-2">
                        <div class="relative">
                            <img class="h-auto max-w-full rounded-lg"
                                :src="inventoryStore.selectedImage ?? '/images/default.jpg'"
                                onerror="if (this.src != '/images/default.jpg') this.src = '/images/default.jpg';">
                            <span aria-hidden="true" class="absolute inset-0" />
                        </div>
                        <div class="grid grid-cols-5 gap-2">
                            <div v-for="image in inventoryStore.product.product_images" :key="image.Id"
                                class="relative cursor-pointer"
                                @click="changeImage(inventoryStore.product.UrlServerImage + image.name)">
                                <img class="object-cover w-full h-full rounded-lg aspect-[1/1]"
                                    :src="(inventoryStore.product.UrlServerImage + image.name) ?? '/images/default.jpg'"
                                    onerror="if (this.src != '/images/default.jpg') this.src = '/images/default.jpg';">
                                <span aria-hidden="true" class="absolute inset-0" />
                            </div>
                        </div>

                    </div>

                </div>

                <div class="mt-6 sm:mt-8 lg:mt-0">
                    <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl ">
                        {{ inventoryStore.product.Product }}
                    </h1>
                    <div class="mt-4 grid justify-items-start gap-1">
                        <p class="text-2xl font-extrabold text-gray-900 sm:text-3xl">
                            {{ formatCurrency((inventoryStore.totalNumberProducts >= 3 ?
                                inventoryStore.product.Price_Wholesale : inventoryStore.product.Current_Price)) }}
                        </p>

                        <div v-if="inventoryStore.totalNumberProducts >= 3">
                            <p class="text-base font-semibold sm:text-xl text-gray-400 line-through">
                                {{ formatCurrency(inventoryStore.product.Previous_Price) }}
                            </p>
                            <p class="text-xs">Precio exclusivo para mayoristas.</p>
                        </div>

                        <div v-else>
                            <p class="text-base font-semibold sm:text-base">
                                {{ formatCurrency(inventoryStore.product.Price_Wholesale) }} X 3 Und
                            </p>
                            <p class="text-xs">Al comprar 3 o más unidades de cualquier referencia, recibirás un
                                descuento de mayorista.</p>
                        </div>
                    </div>

                    <!-- Colors -->
                    <div class="mt-6">
                        <h3 class="font-medium text-gray-900">Color</h3>

                        <fieldset aria-label="Choose a color" class="mt-4">
                            <div class="flex items-center space-x-3">
                                <label v-for="color in uniqueColors" :key="color?.color_id"
                                    :aria-label="color.color_name"
                                    class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5"
                                    :style="color?.color_id === state?.color?.color_id ?
                                        'border: 2px solid #00BBFF' :
                                        'border: 2px solid transparent'
                                        ">
                                    <input type="radio" name="color-choice" :value="color" v-model="state.color"
                                        class="sr-only">
                                    <span aria-hidden="true"
                                        class="h-8 w-8 rounded-full border border-black border-opacity-10"
                                        :style="{ backgroundColor: color.color }"></span>
                                </label>
                            </div>
                        </fieldset>
                    </div>

                    <!-- Sizes -->
                    <div class="mt-10">
                        <h3 class="font-medium text-gray-900">Talla</h3>

                        <fieldset aria-label="Choose a size" class="mt-4">
                            <div class="grid grid-cols-6 gap-2 sm:grid-cols-7 lg:grid-cols-8">
                                <label v-for="size in uniqueSize" :key="size?.size_id" :aria-label="size.size_name"
                                    :class="'group relative flex items-center justify-center rounded-md border bg-white px-4 py-3 text-sm font-medium uppercase text-gray-900 ' +
                                        (size.active ? 'hover:bg-gray-50 focus:outline-none cursor-pointer' : 'cursor-not-allowed opacity-30')
                                        " :style="size?.size_id == state?.size?.size_id ?
                                            'border: 2px solid #00BBFF' :
                                            null
                                            ">

                                    <input type="radio" name="size-choice" :value="size" v-model="state.size"
                                        class="sr-only" :disabled="!size.active">
                                    <span>{{ size.size }} </span>
                                    <span class="pointer-events-none absolute -inset-px rounded-md"
                                        aria-hidden="true"></span>
                                </label>
                            </div>
                        </fieldset>
                    </div>

                    <div class="mt-6 sm:gap-4 sm:items-center sm:flex sm:mt-8">
                        <a :class="(
                            inventoryStore.product.UnitsGesadmin <= 0 ?
                                'text-gray-400 border-gray-50 bg-gray-50 cursor-not-allowed' :
                                'text-gray-900 border-gray-200 bg-white hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-100 cursor-pointer') +
                            ' flex items-center justify-center py-2.5 px-5 text-sm font-medium focus:outline-none rounded-lg border'"
                            role="button"
                            @click="inventoryStore.addToShoppingCart(inventoryStore.product, state.color, state.size)">
                            <svg class="w-5 h-5 -ms-2 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                width="24" height="24" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4h1.5L8 16m0 0h8m-8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm8 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4Zm.75-3H7.5M11 7H6.312M17 4v6m-3-3h6" />
                            </svg>
                            Agregar al carrito
                        </a>
                    </div>

                    <hr class="my-6 md:my-8 border-gray-200" />

                    <p class="mb-6 text-gray-500 ">
                        {{ inventoryStore.product.Description }}
                    </p>
                </div>
            </div>
        </div>
    </section>
    <div v-else class="mt-12 mb-6 grid justify-center">
        <div style="border-top-color:transparent" class="w-8 h-8 border-4 border-gray-300 rounded-full animate-spin">
        </div>
    </div>
</template>
