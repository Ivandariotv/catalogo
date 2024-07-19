<script setup>
import { useInventoryStore } from "../stores/Inventory.js";
import { onMounted } from "vue";

const inventoryStore = useInventoryStore();

// carga el contenido de la pagina
onMounted(() => {
    inventoryStore.getProducts();
});

const products = [
    {
        id: 1,
        name: 'Basic Tee',
        href: '#',
        imageSrc: 'https://aguamarinaoficial.com/cdn/shop/files/2_262433ce-998c-44ea-a06c-5989c0529a0f.png?v=1707940016',
        imageAlt: "Front of men's Basic Tee in black.",
        price: '$35',
        color: 'Black',
    },
    {
        id: 1,
        name: 'Basic Tee',
        href: '#',
        imageSrc: 'https://aguamarinaoficial.com/cdn/shop/files/8FEB16730copia.jpg?v=1707940987',
        imageAlt: "Front of men's Basic Tee in black.",
        price: '$35',
        color: 'Black',
    },
    {
        id: 1,
        name: 'Basic Tee',
        href: '#',
        imageSrc: 'https://aguamarinaoficial.com/cdn/shop/files/AGUAMARINA14924copia.jpg?v=1708115260',
        imageAlt: "Front of men's Basic Tee in black.",
        price: '$35',
        color: 'Black',
    },
    {
        id: 1,
        name: 'Basic Tee',
        href: '#',
        imageSrc: 'https://aguamarinaoficial.com/cdn/shop/files/8FEB16844copia.jpg?v=1708012912',
        imageAlt: "Front of men's Basic Tee in black.",
        price: '$35',
        color: 'Black',
    },
    {
        id: 1,
        name: 'Basic Tee',
        href: '#',
        imageSrc: 'https://aguamarinaoficial.com/cdn/shop/files/F004FX-3.jpg?v=1708369830',
        imageAlt: "Front of men's Basic Tee in black.",
        price: '$35',
        color: 'Black',
    },
    {
        id: 1,
        name: 'Body Con Transparencias Y Encaje',
        href: '#',
        imageSrc: 'https://aguamarinaoficial.com/cdn/shop/files/AGUAMARINA15190copia.jpg?v=1720556545',
        imageAlt: "Front of men's Basic Tee in black.",
        price: '$ 31.000',
        color: 'Black',
    },
]
</script>

<template>
    <div class="mt-6 grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3 lg:grid-cols-4 xl:gap-x-8">
        <div v-for="product in inventoryStore.products.data" :key="product.id" class="group relative">
            <div
                class="aspect-h-1 aspect-w-1 w-full overflow-hidden rounded-md bg-gray-200 lg:aspect-none group-hover:opacity-75 lg:h-80">
                <img :src="`https://${product.UrlImage}`" :alt="product.Product"
                    class="h-full w-full object-cover object-center lg:h-full lg:w-full" />
            </div>
            <div class="mt-4 flex justify-between">
                <div>
                    <h3 class="text-sm text-gray-700">
                        <a href="#">
                            <span aria-hidden="true" class="absolute inset-0" />
                            {{ product.Product }}
                        </a>
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">{{ product.Description }}</p>
                </div>
                <p class="text-sm font-medium text-gray-900">{{ product.Current_Price }}</p>
            </div>
        </div>
    </div>
</template>
