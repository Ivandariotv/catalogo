import { defineStore } from "pinia";
import { ref } from "vue";
import axios from "axios";

export const useInventoryStore = defineStore("inventory", {
    state: () => ({
        loadingProducts: true,
        categories: {},
    }),

    getters: {},

    actions: {
        /** Obtiene los productos */
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
    },
});
