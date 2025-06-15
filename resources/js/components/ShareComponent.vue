<template>
    <div>

        <div class="mt-6 sm:gap-4 sm:items-center sm:flex sm:mt-8">
            <a class="text-gray-900 border-gray-200 bg-white hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-100 cursor-pointer flex items-center justify-center py-2.5 px-5 text-sm font-medium focus:outline-none rounded-lg border"
                role="button" @click="open = true">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                </svg>
            </a>
        </div>
        <TransitionRoot as="template" :show="open">
            <Dialog class="relative z-10" @close="open = false">
                <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0"
                    enter-to="opacity-100" leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                    <div class="fixed inset-0 bg-gray-500/75 transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                    <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                        <TransitionChild as="template" enter="ease-out duration-300"
                            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                            enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                            leave-from="opacity-100 translate-y-0 sm:scale-100"
                            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                            <DialogPanel
                                class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg  w-full">
                                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 w-full">
                                    <div class="sm:flex sm:items-start w-full">

                                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                            <DialogTitle as="h3" class="text-base font-semibold text-gray-900">
                                                Compartir este producto</DialogTitle>

                                            <div class="mt-2 relative w-full bg-gray-50 rounded-lg">
                                                <input id="npm-install-copy-text" type="text"
                                                    class="col-span-6 bg-gray-50 border border-gray-300 text-gray-500 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full px-4 py-4"
                                                    :value="textToCopy" disabled readonly>
                                                <button @click="copyText"
                                                    class="absolute end-2.5 top-1/2 -translate-y-1/2 text-gray-900 hover:bg-gray-100 rounded-lg py-2 px-4 inline-flex items-center justify-center bg-white border border-gray-200">
                                                    <span v-if="!copied" class="inline-flex items-center">
                                                        <svg class="w-3 h-3 me-1.5" fill="currentColor"
                                                            viewBox="0 0 18 20">
                                                            <path d="M16 1h-3.278A1.992 1.992 0 0 0 11 0H7a1.993 1.993 0 0 0-1.722 1H2a2 2 0 0 0-2
                                                                2v15a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2Zm-3 14H5a1 1 0 0 1 0-2h8a1
                                                                1 0 0 1 0 2Zm0-4H5a1 1 0 0 1 0-2h8a1 1 0 1 1 0 2Zm0-5H5a1 1 0 0 1 0-2h2V2h4v2h2a1
                                                                1 0 1 1 0 2Z" />
                                                        </svg>
                                                        <span class="text-xs font-semibold">Copiar</span>
                                                    </span>
                                                    <span v-else class="inline-flex items-center text-blue-700">
                                                        <svg class="w-3 h-3 me-1.5" fill="none" viewBox="0 0 16 12"
                                                            stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M1 5.917 5.724 10.5 15 1.5" />
                                                        </svg>
                                                        <span class="text-xs font-semibold">Copiado</span>
                                                    </span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                    <button type="button"
                                        class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-xs ring-1 ring-gray-300 ring-inset hover:bg-gray-50 sm:mt-0 sm:w-auto"
                                        @click="open = false" ref="cancelButtonRef">Cancel</button>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { ExclamationTriangleIcon } from '@heroicons/vue/24/outline'

const open = ref(false)
const copied = ref(false)
const textToCopy = ref(window.location.href) // puedes pasar esto como prop si deseas

function copyText() {
    navigator.clipboard.writeText(textToCopy.value).then(() => {
        copied.value = true
        setTimeout(() => (copied.value = false), 2000)
    })
}
</script>
