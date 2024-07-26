<script setup>
import { Disclosure } from '@headlessui/vue'
import { MagnifyingGlassIcon } from '@heroicons/vue/24/outline'
import { useInventoryStore } from "../stores/Inventory.js";
import ShoppingCart from "./ShoppingCartComponent.vue";
import { onMounted, ref, reactive } from "vue";
import { useRouter } from 'vue-router';

const inventoryStore = useInventoryStore();
const router = useRouter();

const scrollContainer = ref(null);
const state = reactive({
    atStart: true,
    atEnd: true,
});


const scrollLeft = () => {
    if (scrollContainer.value) {
        scrollContainer.value.scrollBy({ left: -200, behavior: 'smooth' });
    }
};

const scrollRight = () => {
    if (scrollContainer.value) {
        scrollContainer.value.scrollBy({ left: 200, behavior: 'smooth' });
    }
};

const updateScrollState = () => {
    if (scrollContainer.value) {
        state.atStart = scrollContainer.value.scrollLeft === 0;
        state.atEnd = scrollContainer.value.scrollLeft + scrollContainer.value.clientWidth >= scrollContainer.value.scrollWidth;
    }
};

const navigateToCategory = (categoryId) => {
    let path = '/';

    if (categoryId) {
        path = `/categories/${categoryId}`;
    }

    router.push({ path: path });
    inventoryStore.getProducts(categoryId);
};

const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

// carga el contenido de la pagina
onMounted(async () => {
    try {
        // Espera a que getCategories termine de ejecutarse
        await inventoryStore.getCategories();
        await sleep(1000);
        updateScrollState();
    } catch (error) { }
});
</script>

<template>
    <div class="min-h-full">
        <Disclosure as="nav" v-slot="{ open }">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <img class="h-16 w-16" src="/storage/logo.png" alt="Your Company" />
                        </div>
                    </div>
                    <div>
                        <div class="ml-4 flex items-center md:ml-6 ">
                            <div class="relative">
                                <input type="search" id="default-search"
                                    class="block w-full p-[11px] text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50"
                                    placeholder="Buscar" v-model="inventoryStore.search" required />
                                <button @click="inventoryStore.getProductsByKeyword()"
                                    class="text-gray-800 absolute end-1 bottom-1 bg-gray-50 active:bg-gray-200 font-medium rounded-lg text-sm px-1.5 py-1.5">
                                    <MagnifyingGlassIcon class="h-6 w-6" aria-hidden="true" />
                                </button>
                            </div>

                            <ShoppingCart />
                        </div>
                    </div>
                </div>
            </div>
        </Disclosure>

        <header class="bg-white mx-auto max-w-7xl relative">
            <!-- Botones de navegación -->
            <div
                class="absolute left-0 bg-gradient-to-r from-white from-20% lg:from-50% to-transparent to-60% lg:to-60% pl-2 sm:pl-3 lg:pl-4 h-full flex items-center z-10">
                <button @click="scrollLeft"
                    class="w-6 h-6 px-[7.5px] text-white bg-gray-600 hover:bg-gray-800 ring-0 focus:ring-4 focus:outline-none focus:ring-gray-300 rounded-full text-sm text-center inline-flex items-center me-2 transition duration-200 ease-in-out"
                    :style="{ opacity: state.atStart ? '0' : '100' }" :disabled="state.atStart">
                    &#10094;
                </button>
            </div>

            <div
                class="absolute right-0 bg-gradient-to-r from-transparent from-40% lg:from-20% to-white to-80% lg:to-50% pr-2 sm:pr-3 lg:pr-4 h-full flex items-center z-10">
                <button @click="scrollRight"
                    class="w-6 h-6 px-[9px] text-white bg-gray-600 hover:bg-gray-800 ring-0 focus:ring-4 focus:outline-none focus:ring-gray-300 rounded-full text-sm text-center inline-flex items-center me-2 transition duration-200 ease-in-out"
                    :style="{ opacity: state.atEnd ? '0' : '100' }" :disabled="state.atEnd">
                    &#10095;
                </button>
            </div>

            <div ref="scrollContainer"
                class="scroll-pl-4 mx-1 sm:scroll-pl-6 lg:scroll-pl-8 mx-auto max-w-7xl px-3 sm:px-6 lg:px-8 mx-auto max-w-7xl py-6 flex gap-4 sm:gap-6 lg:gap-8 overflow-hidden overflow-x-auto snap-x"
                @scroll="updateScrollState" style="scrollbar-width: none;">
                <div
                    :class="(inventoryStore.selectedCategory == null ? 'text-gray-950  font-bold' : 'text-gray-400') + ' cursor-pointer hover:text-gray-950 hover:font-bold group relative snap-start transition duration-200 ease-in-out'">
                    <div :class="(inventoryStore.selectedCategory == null ? 'border-[#00BBFF]' : '') + ' bg-cover w-20 h-20 bg-center overflow-hidden rounded-full border-2 group-hover:border-[#00BBFF] transition duration-200 ease-in-out'"
                        :style="{ backgroundImage: 'url(/storage/menu_all.jpg)' }" @click="navigateToCategory(null)">
                    </div>
                    <p class="w-20 text-sm text-center pt-2 line-clamp-2">Todo</p>
                </div>

                <div :class="(inventoryStore.selectedCategory == category.Code ? 'text-gray-950 font-bold' : 'text-gray-400') + ' cursor-pointer hover:text-gray-950 hover:font-bold group relative snap-start transition duration-200 ease-in-out'"
                    v-for="category in inventoryStore.categories" :key="category.Code"
                    @click="navigateToCategory(category.Code)">
                    <div
                        :class="(inventoryStore.selectedCategory == category.Code ? 'border-[#00BBFF]' : '') + ' bg-cover w-20 h-20 bg-center overflow-hidden rounded-full border-2 group-hover:border-[#00BBFF] transition duration-200 ease-in-out'">

                        <img :src="category.UrlImage ?? '/storage/menu_all.jpg'" :alt="category.Name"
                            class="h-full w-full object-cover object-center" onerror="
                            if (this.src != '/storage/menu_all.jpg') this.src = '/storage/menu_all.jpg';
                        " />
                    </div>
                    <p class="w-20 text-sm text-center pt-2 line-clamp-2">
                        <span aria-hidden="true" class="absolute inset-0" />
                        {{ category.Name }}
                    </p>
                </div>
            </div>
        </header>
        <main>
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <slot name="content"></slot>
            </div>
        </main>
    </div>
</template>

<style>
/* Quitar el borde al seleccionar */
#default-search:focus {
    outline: none;
    box-shadow: none;
}
</style>
