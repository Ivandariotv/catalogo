<script setup>
import { useInventoryStore } from "../stores/Inventory.js";
import { useRouter } from 'vue-router';
import { onMounted, ref, defineProps } from "vue";

const inventoryStore = useInventoryStore();
const router = useRouter();

const props = defineProps({
    categoryId: {
        type: [String, Number],
        required: false
    }
});

// carga el contenido de la pagina
onMounted(() => {
    inventoryStore.getProducts(props.categoryId);
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

const navigateToProduct = (productId) => {
    router.push({ path: `/product/${productId}` });
};
</script>

<template>
    <div class="grid grid-cols-2 gap-x-6 gap-y-10 sm:grid-cols-3 lg:grid-cols-4 xl:gap-x-8">
        <div v-for="product in inventoryStore.products" :key="product.Id" class="group relative"
            @click="navigateToProduct(product.Id)">
            <div
                class="aspect-h-1 aspect-w-1 w-full overflow-hidden rounded-md bg-gray-200 aspect-none group-hover:opacity-75 md:h-80 sm:h-60 h-[200px] relative">
                <p class="flex h-10 items-center justify-center bg-gray-600 px-2 text-sm font-medium text-white sm:px-4 lg:px-6 absolute"
                    v-if="product.UnitsGesadmin <= 0">Sin unidades</p>
                <img :src="product.UrlImage ?? '/images/default.jpg'" :alt="product.Product"
                    class="h-full w-full object-cover object-center lg:h-full lg:w-full" onerror="
                        if (this.src != '/images/default.jpg') this.src = '/images/default.jpg';
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
                    <p class="mt-1 text-sm text-gray-500 line-clamp-1">{{ product.Description }}</p>
                </div>
                <div class="grid justify-items-end">
                    <p class="text-sm font-bold text-gray-900">{{ formatCurrency((inventoryStore.totalNumberProducts >= 6 ? product.Price_Wholesale : product.Current_Price)) }}</p>
                    <p class="text-xs text-gray-400 line-through"
                        v-if="inventoryStore.totalNumberProducts >= 6">
                        {{ formatCurrency(product.Previous_Price) }}</p>
                    <p class="text-xs rounded-s-lg" v-else>
                        {{ formatCurrency(product.Price_Wholesale) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div v-if="inventoryStore.loadingProducts || (inventoryStore.products.length > 0 && inventoryStore.nextProductPageURL != null)"
        class="mt-12 mb-6 grid justify-center">
        <a class="
                cursor-pointer
                inline-block
                rounded-md
                border
                border-transparent
                bg-gray-50
                px-8
                py-3
                text-center
                font-medium
                text-gray
                hover:bg-gray-100
            " v-if="!inventoryStore.loadingProducts" @click="inventoryStore.getMoreProducts()">Cargar más productos</a>

        <div v-else style="border-top-color:transparent"
            class="w-8 h-8 border-4 border-gray-300 rounded-full animate-spin">
        </div>
    </div>

    <div v-if="inventoryStore.products.length == 0" class="mt-6 mb-6 grid justify-center">
        Productos no disponibles.
    </div>
</template>
