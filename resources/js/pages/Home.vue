<script setup>
import { useInventoryStore } from "../stores/Inventory.js";
import { onMounted } from "vue";

const inventoryStore = useInventoryStore();

// carga el contenido de la pagina
onMounted(() => {
    inventoryStore.getProducts();
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
</script>

<template>
    <div class="mt-6 grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3 lg:grid-cols-4 xl:gap-x-8">
        <div v-for="product in inventoryStore.products.data" :key="product.id" class="group relative">
            <div
                class="aspect-h-1 aspect-w-1 w-full overflow-hidden rounded-md bg-gray-200 lg:aspect-none group-hover:opacity-75 lg:h-80">
                <img :src="product.UrlImage ?? '/storage/default.jpg'" :alt="product.Product"
                    class="h-full w-full object-cover object-center lg:h-full lg:w-full" onerror="
                        if (this.src != '/storage/default.jpg') this.src = '/storage/default.jpg';
                    " />
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
                <div class="grid justify-items-end">
                    <p class="text-xs text-gray-400 line-through" v-if="product.Current_Price != product.Previous_Price"> {{ formatCurrency(product.Previous_Price) }}</p>
                    <p class="text-sm font-bold text-gray-900">{{ formatCurrency(product.Current_Price) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
