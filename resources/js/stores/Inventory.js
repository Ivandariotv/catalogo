import { defineStore } from "pinia";
import { ref } from "vue";
import axios from "axios";
import Product from "../pages/Product.vue";

export const useInventoryStore = defineStore("inventory", {
    state: () => ({
        loadingProducts: true,
        categories: [],
        products: [],
        nextProductPageURL: null,
        selectedCategory: null,
        product: null,
        loadingProduct: true,
        selectedImage: null,
        shoppingCart: [],
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
        async getProducts(categoryId) {
            this.loadingProducts = true;
            this.products = {};
            let url = '/api/Products?order=desc';
            this.selectedCategory = null;

            if (categoryId) {
                this.selectedCategory = categoryId;
                url = `/api/Products/Category/${categoryId}?order=desc`;
            }

            axios({
                method: "get",
                url: url,
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

        /** Obtiene un producto completo */
        async getProduct(productId) {
            this.loadingProduct = true;
            this.selectedImage = null;
            this.product = null;

            axios({
                method: "get",
                url: `/api/Product/${productId}`,
            }).then(({ data }) => {
                this.product = data;
                this.selectedImage = data.UrlServerImage + data.product_images[0].name;
                this.loadingProduct = false;
            });
        },

        addToShoppingCart(product) {
            // Buscar el producto en el carrito por su ID
            const existingProductIndex = this.shoppingCart.findIndex(item => item.Id === product.Id);

            if (existingProductIndex !== -1) {
                // Si el producto ya existe en el carrito, incrementar su cantidad de unidades
                this.shoppingCart[existingProductIndex].units += 1;
            } else {
                // Si el producto no existe en el carrito, agregarlo con una unidad
                product.units = 1;
                this.shoppingCart = [...this.shoppingCart, product];
            }
        }
    },
});
