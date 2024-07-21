import { defineStore } from "pinia";
import { ref } from "vue";
import axios from "axios";

export const useInventoryStore = defineStore("inventory", {
    state: () => ({
        loadingProducts: true,
        categories: {},
        products: {},
        nextProductPageURL: null,
    }),

    getters: {},

    actions: {
        /** Obtiene las categorias */
        async getCategories() {
            axios({
                method: "get",
                url: "/api/Categories?page=1",
            }).then(({ data }) => {
                this.categories = data;
            });
        },

        /** Obtiene los productos */
        async getProducts() {
            this.loadingProducts = true;

            axios({
                method: "get",
                url: "/api/Products?order=desc",
                params: {
                    order: 'desc'
                }
            }).then(({ data }) => {
                this.products = data.data;
                this.nextProductPageURL = data.next_page_url;
                this.loadingProducts = false;
            });
        },

        /** Obtiene más productos y los agrega al array */
        async getMoreProducts() {
            this.loadingProducts = true;

            axios({
                method: "get",
                url: this.nextProductPageURL,
            }).then(({ data }) => {
                this.products = [...this.products, ...data.data];
                this.nextProductPageURL = data.next_page_url;
                this.loadingProducts = false;
            });
        },
    },
});
