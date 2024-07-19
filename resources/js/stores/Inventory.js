import { defineStore } from "pinia";
import { ref } from "vue";
import axios from "axios";

export const useInventoryStore = defineStore("inventory", {
    state: () => ({
        loadingProducts: true,
        categories: {},
        products: {}
    }),

    getters: {},

    actions: {
        /** Obtiene las categorias */
        async getCategories() {
            this.loadingProducts = true;

            axios({
                method: "get",
                url: "/api/Categories?page=1",
            }).then(({ data }) => {
                this.categories = data;
                this.loadingProducts = false;
            });
        },

        /** Obtiene los productos */
        async getProducts() {
            this.loadingProducts = true;

            axios({
                method: "get",
                url: "/api/Products?page=1",
            }).then(({ data }) => {
                this.products = data;
                this.loadingProducts = false;
            });
        },
    },
});
