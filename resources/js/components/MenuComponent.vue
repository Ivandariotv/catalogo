<script setup>
import { Disclosure } from '@headlessui/vue'
import { MagnifyingGlassIcon, ShoppingCartIcon } from '@heroicons/vue/24/outline'
import { useInventoryStore } from "../stores/Inventory.js";
import { onMounted } from "vue";

const inventoryStore = useInventoryStore();

// carga el contenido de la pagina
onMounted(() => {
    inventoryStore.getCategories();
});
</script>

<template>
    <div class="min-h-full">
        <Disclosure as="nav" v-slot="{ open }">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <img class="h-16 w-16"
                                src="/storage/logo.png"
                                alt="Your Company" />
                        </div>
                    </div>
                    <div>
                        <div class="ml-4 flex items-center md:ml-6 ">
                            <button type="button" class="relative p-2 text-gray-900 hover:text-gray-600">
                                <MagnifyingGlassIcon class="h-6 w-6" aria-hidden="true" />
                            </button>
                            <button type="button" class="relative p-2 text-gray-900 hover:text-gray-600">
                                <ShoppingCartIcon class="h-6 w-6" aria-hidden="true" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Disclosure>

        <header class="bg-white ">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex gap-6 overflow-hidden overflow-x-auto custom-scroll">
                <div class="w-20">
                    <div class="bg-cover w-20 h-20 bg-center overflow-hidden rounded-full"
                        :style='{ backgroundImage: `url(/storage/menu_all.jpg)` }'>
                    </div>
                    <p class="font-semibold text-sm text-center pt-2 line-clamp-2">Todo</p>
                </div>
                <div class="w-20" v-for="(category, index) in inventoryStore.categories.data" :key="index">
                    <div class="bg-cover w-20 h-20 bg-center overflow-hidden rounded-full"
                        :style='{ backgroundImage: `url(${category.UrlImage})` }'>
                    </div>
                    <p class="font-semibold text-sm text-center pt-2 line-clamp-2">{{ category.Name }}</p>
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
