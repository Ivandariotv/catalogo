<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { useInventoryStore } from "../stores/Inventory.js";
import { useRouter } from 'vue-router';
import { XMarkIcon, ShoppingCartIcon } from '@heroicons/vue/24/outline'
import { onMounted } from "vue";

const inventoryStore = useInventoryStore();
const router = useRouter();

const navigateToProduct = (productId) => {
    router.push({ path: `/product/${productId}` });
    inventoryStore.getProduct(productId);
    inventoryStore.openShoppingCart = false;
};

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

// carga el contenido de la pagina
onMounted(() => {
    inventoryStore.loadFromLocalStorage();
});

const products = [
    {
        id: 1,
        name: 'Throwback Hip Bag',
        href: '#',
        color: 'Salmon',
        price: '$90.00',
        quantity: 1,
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/shopping-cart-page-04-product-01.jpg',
        imageAlt: 'Salmon orange fabric pouch with match zipper, gray zipper pull, and adjustable hip belt.',
    },
    {
        id: 2,
        name: 'Medium Stuff Satchel',
        href: '#',
        color: 'Blue',
        price: '$32.00',
        quantity: 1,
        imageSrc: 'https://tailwindui.com/img/ecommerce-images/shopping-cart-page-04-product-02.jpg',
        imageAlt:
            'Front of satchel with blue canvas body, black straps and handle, drawstring top, and front zipper pouch.',
    }
]
</script>

<template>
    <button type="button" class="relative p-2 text-gray-900 hover:text-gray-600"
        @click="inventoryStore.openShoppingCart = true">
        <ShoppingCartIcon class="h-6 w-6" aria-hidden="true" />
    </button>

    <TransitionRoot as="template" :show="inventoryStore.openShoppingCart">
        <Dialog class="relative z-10" @close="inventoryStore.openShoppingCart = false">
            <TransitionChild as="template" enter="ease-in-out duration-500" enter-from="opacity-0"
                enter-to="opacity-100" leave="ease-in-out duration-500" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                        <TransitionChild as="template"
                            enter="transform transition ease-in-out duration-500 sm:duration-700"
                            enter-from="translate-x-full" enter-to="translate-x-0"
                            leave="transform transition ease-in-out duration-500 sm:duration-700"
                            leave-from="translate-x-0" leave-to="translate-x-full">
                            <DialogPanel class="pointer-events-auto w-screen max-w-md">
                                <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                                    <div class="flex-1 overflow-y-auto px-4 py-6 sm:px-6">
                                        <div class="flex items-start justify-between">
                                            <DialogTitle class="text-lg font-medium text-gray-900">Carrito de compras
                                            </DialogTitle>
                                            <div class="ml-3 flex h-7 items-center">
                                                <button type="button"
                                                    class="relative -m-2 p-2 text-gray-400 hover:text-gray-500"
                                                    @click="inventoryStore.openShoppingCart = false">
                                                    <span class="absolute -inset-0.5" />
                                                    <span class="sr-only">Close panel</span>
                                                    <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mt-8">
                                            <div class="flow-root">
                                                <ul role="list" class="-my-6 divide-y divide-gray-200">
                                                    <li v-for="product in inventoryStore.shoppingCart" :key="product.Id"
                                                        v-if="inventoryStore.shoppingCart.length > 0" class="flex py-6">
                                                        <div
                                                            class="h-24 w-24 flex-shrink-0 overflow-hidden rounded-md border border-gray-200 relative">
                                                            <img :src="product.UrlServerImage + product.product_images[0].name"
                                                                class="h-full w-full object-cover object-center" />
                                                            <span aria-hidden="true" class="absolute inset-0" />
                                                        </div>

                                                        <div class="ml-4 flex flex-1 flex-col">
                                                            <div>
                                                                <div
                                                                    class="flex justify-between text-base font-medium text-gray-900">
                                                                    <h3 class="line-clamp-2">
                                                                        <a @click="navigateToProduct(product.Id)"
                                                                            class="cursor-pointer">{{ product.Product }}
                                                                        </a>
                                                                    </h3>
                                                                    <div class="ml-4 grid justify-items-end">
                                                                        {{ formatCurrency(product.Current_Price) }}
                                                                        <p class="text-sm text-gray-400 line-through"
                                                                            v-if="product.Current_Price != product.Previous_Price">
                                                                            {{ formatCurrency(product.Previous_Price) }}
                                                                        </p>
                                                                    </div>

                                                                </div>
                                                                <p class="mt-1 text-sm text-gray-500 line-clamp-1">
                                                                    {{ product.Description }}</p>
                                                            </div>
                                                            <div class="flex flex-1 items-end justify-between text-sm">
                                                                <div class="flex items-center">
                                                                    <button
                                                                        class="group rounded-l-xl px-1 py-1 border border-gray-200 flex items-center justify-center shadow-sm shadow-transparent transition-all duration-500 hover:bg-gray-50 hover:border-gray-300 hover:shadow-gray-300 focus-within:outline-gray-300"
                                                                        @click="inventoryStore.removeFromShoppingCart(product)">
                                                                        <svg class="stroke-gray-900 transition-all duration-500 group-hover:stroke-black"
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            width="18" height="18" viewBox="0 0 22 22"
                                                                            fill="none">
                                                                            <path d="M16.5 11H5.5" stroke=""
                                                                                stroke-width="1.6"
                                                                                stroke-linecap="round" />
                                                                            <path d="M16.5 11H5.5" stroke=""
                                                                                stroke-opacity="0.2" stroke-width="1.6"
                                                                                stroke-linecap="round" />
                                                                            <path d="M16.5 11H5.5" stroke=""
                                                                                stroke-opacity="0.2" stroke-width="1.6"
                                                                                stroke-linecap="round" />
                                                                        </svg>
                                                                    </button>
                                                                    <input type="text"
                                                                        class="border-y border-gray-200 outline-none text-gray-900 font-semibold text-sm w-6 placeholder:text-gray-900 py-1 text-center bg-transparent"
                                                                        placeholder="1" v-model="product.units">
                                                                    <button
                                                                        class="group rounded-r-xl px-1 py-1 border border-gray-200 flex items-center justify-center shadow-sm shadow-transparent transition-all duration-500 hover:bg-gray-50 hover:border-gray-300 hover:shadow-gray-300 focus-within:outline-gray-300"
                                                                        @click="inventoryStore.addToShoppingCart(product)">
                                                                        <svg class="stroke-gray-900 transition-all duration-500 group-hover:stroke-black"
                                                                            xmlns="http://www.w3.org/2000/svg"
                                                                            width="18" height="18" viewBox="0 0 22 22"
                                                                            fill="none">
                                                                            <path d="M11 5.5V16.5M16.5 11H5.5" stroke=""
                                                                                stroke-width="1.6"
                                                                                stroke-linecap="round" />
                                                                            <path d="M11 5.5V16.5M16.5 11H5.5" stroke=""
                                                                                stroke-opacity="0.2" stroke-width="1.6"
                                                                                stroke-linecap="round" />
                                                                            <path d="M11 5.5V16.5M16.5 11H5.5" stroke=""
                                                                                stroke-opacity="0.2" stroke-width="1.6"
                                                                                stroke-linecap="round" />
                                                                        </svg>
                                                                    </button>
                                                                </div>

                                                                <div class="flex">
                                                                    <button type="button"
                                                                        @click="inventoryStore.removeItemFromShoppingCart(product)"
                                                                        class="font-medium text-red-500 hover:text-red-800">Eliminar</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <p v-else class="py-6 text-sm">Aún no has seleccionado productos.
                                                    </p>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-t border-gray-200 px-4 py-6 sm:px-6">
                                        <div class="flex justify-between text-base font-medium text-gray-900">
                                            <p>Subtotal</p>
                                            <p>{{ formatCurrency(inventoryStore.subtotal) }}</p>
                                        </div>
                                        <p class="mt-0.5 text-sm text-gray-500">La compra continuará por WhatsApp.</p>
                                        <div class="mt-6">
                                            <a href="#"
                                                class="flex items-center justify-center rounded-md border border-transparent bg-gray-800 px-6 py-3 text-base font-medium text-white shadow-sm hover:bg-gray-950">Verificar</a>
                                        </div>
                                        <div class="mt-6 flex justify-center text-center text-sm text-gray-500">
                                            <p>
                                                o{{ ' ' }}
                                                <button type="button"
                                                    class="font-medium text-gray-800 hover:text-gray-950"
                                                    @click="inventoryStore.openShoppingCart = false">
                                                    Seguir Comprando
                                                    <span aria-hidden="true"> &rarr;</span>
                                                </button>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
