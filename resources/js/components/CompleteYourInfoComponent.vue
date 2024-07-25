<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'
import { useInventoryStore } from "../stores/Inventory.js";

const inventoryStore = useInventoryStore();

const validateNumber = (event) => {
  event.target.value = event.target.value.replace(/[^0-9]/g, '')
  inventoryStore.yourInformation.whatsapp = event.target.value
}
</script>

<template>
    <TransitionRoot as="template" :show="inventoryStore.openCompleteYourInfo">
        <Dialog class="relative z-10" @close="inventoryStore.openCompleteYourInfo = false">
            <TransitionChild as="template" enter="ease-out duration-1000" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel
                            class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                            <div class="bg-white px-4 pb-4 sm:p-6 sm:pb-4">
                                <div class="sm:flex sm:items-start">
                                    <div class="mt-3 sm:ml-4 sm:mt-0 sm:text-left">
                                        <h2
                                            class="mt-10 text-center text-2xl font-bold leading-9 tracking-tight text-gray-900">
                                            Completa tu información</h2>
                                        <div class="mt-4">
                                            <div>
                                                <label for="name"
                                                    class="block text-sm font-medium leading-6 text-gray-900">Ingresa tu
                                                    nombre y tu apellido
                                                </label>
                                                <div class="mt-1">
                                                    <input id="name" name="name" type="text"
                                                        v-model="inventoryStore.yourInformation.name" required
                                                        class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                                </div>
                                            </div>

                                            <div class="mt-2">
                                                <label for="whatsapp"
                                                    class="block text-sm font-medium leading-6 text-gray-900">Ingresa tu
                                                    Whatsapp
                                                </label>
                                                <div class="mt-1">
                                                    <input id="whatsapp" name="whatsapp" type="tel" @input="validateNumber"
                                                        v-model="inventoryStore.yourInformation.whatsapp" required
                                                        class="block w-full rounded-md border-0 py-1.5 px-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" />
                                                </div>
                                            </div>

                                            <div class="relative flex gap-x-3 mt-2">
                                                <div class="text-sm leading-6">
                                                    <p class="text-gray-500">Al continuar tu compra, tu carrito de
                                                        compras se vaciará y serás dirigido al chat con un asesor en
                                                        WhatsApp.</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                <button type="button"
                                    class="inline-flex w-full justify-center rounded-md bg-gray-700 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-950 sm:ml-3 sm:w-auto"
                                    @click="inventoryStore.finishShopping()">Continuar</button>
                                <button type="button"
                                    class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                    @click="inventoryStore.openCompleteYourInfo = false"
                                    ref="cancelButtonRef">Cancel</button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
