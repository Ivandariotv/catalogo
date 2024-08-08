<template>
    <div id="animation-carousel" class="relative w-full px-3 sm:px-6 lg:px-8" data-carousel="static" ref="carousel">
        <!-- Carousel wrapper -->
        <div class="relative h-56 overflow-hidden rounded-lg md:h-96">
            <!-- Carousel items -->
            <div v-for="(item, index) in inventoryStore.banner" :key="index"
                :class="['duration-200 ease-linear', { 'hidden': currentIndex !== index }]" data-carousel-item>
                <img :src="item.UrlImage"
                    class="absolute block w-full h-full object-cover object-center -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2"
                    :alt="item.Name">
                <span aria-hidden="true" class="absolute inset-0" />
            </div>
        </div>
        <!-- Slider controls -->
        <button type="button" v-if="inventoryStore.banner.length > 1"
            class="absolute top-0 start-0 z-2 flex items-center justify-center h-full px-5 sm:px-8 lg:px-10 cursor-pointer group focus:outline-none"
            @click="prev">
            <span
                class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-500 group-hover:bg-gray-400">
                <svg class="w-4 h-4 text-white rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 1 1 5l4 4" />
                </svg>
                <span class="sr-only">Previous</span>
            </span>
        </button>
        <button type="button" v-if="inventoryStore.banner.length > 1"
            class="absolute top-0 end-0 z-2 flex items-center justify-center h-full px-5 sm:px-8 lg:px-10 cursor-pointer group focus:outline-none"
            @click="next">
            <span
                class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gray-500 group-hover:bg-gray-400">
                <svg class="w-4 h-4 text-white rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 6 10">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 9 4-4-4-4" />
                </svg>
                <span class="sr-only">Next</span>
            </span>
        </button>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useInventoryStore } from "../stores/Inventory.js";

const inventoryStore = useInventoryStore();
const currentIndex = ref(0);

const showSlide = (index) => {
    currentIndex.value = index;
};

const prev = () => {
    currentIndex.value = (currentIndex.value - 1 + inventoryStore.banner.length) % inventoryStore.banner.length;
};

const next = () => {
    currentIndex.value = (currentIndex.value + 1) % inventoryStore.banner.length;
};

onMounted(() => {
    showSlide(currentIndex.value);
    inventoryStore.getBanners();
});
</script>
