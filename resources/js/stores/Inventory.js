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
        openShoppingCart: false,
        openCompleteYourInfo: false,
        yourInformation: {
            name: '',
            whatsapp: ''
        },
        companyWhatsapp: null,
        search: "",
    }),

    getters: {
        subtotal: (state) => {
            return state.shoppingCart.reduce((total, product) => {
                return total + (product.Current_Price * product.units);
            }, 0);
        }
    },

    actions: {
        /** Obtiene whatsapp de la empresa */
        async getApplicationSettings() {
            axios({
                method: "get",
                url: "/api/applicationSettings",
            }).then(({ data }) => {
                this.companyWhatsapp = data.whatsapp.number;
            });
        },


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

        async getProductsByKeyword() {
            if (this.search != "") {
                this.loadingProducts = true;
                this.products = {};

                axios({
                    method: "get",
                    url: `/api/Products/Search/${this.search}`,
                }).then(({ data }) => {
                    this.products = data.data;
                    this.nextProductPageURL = data.next_page_url;
                    this.loadingProducts = false;
                });
            } else {
                this.getProducts(null);
            }
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
            this.openShoppingCart = true;

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

            this.saveToLocalStorage(this.shoppingCart);
        },

        removeFromShoppingCart(product) {
            const existingProductIndex = this.shoppingCart.findIndex(item => item.Id === product.Id);

            if (existingProductIndex !== -1) {
                // Si el producto ya existe en el carrito, disminuir su cantidad de unidades
                this.shoppingCart[existingProductIndex].units -= 1;

                // Si la cantidad de unidades es 0, eliminar el producto del carrito
                if (this.shoppingCart[existingProductIndex].units === 0) {
                    this.shoppingCart.splice(existingProductIndex, 1);
                }
            }

            this.saveToLocalStorage(this.shoppingCart);
        },

        removeItemFromShoppingCart(product) {
            const existingProductIndex = this.shoppingCart.findIndex(item => item.Id === product.Id);

            if (existingProductIndex !== -1) {
                // Si el producto ya existe en el carrito, lo elimina
                this.shoppingCart.splice(existingProductIndex, 1);
            }

            this.saveToLocalStorage(this.shoppingCart);
        },

        // Función auxiliar para guardar el estado en Local Storage
        saveToLocalStorage(cart) {
            localStorage.setItem('shoppingCart', JSON.stringify(cart));
        },

        // Función auxiliar para cargar el estado desde Local Storage
        loadFromLocalStorage() {
            const cart = localStorage.getItem('shoppingCart');
            this.shoppingCart = cart ? JSON.parse(cart) : [];
        },

        verifyShoppingCart() {
            if (this.shoppingCart.length > 0) {
                this.openShoppingCart = false;
                this.openCompleteYourInfo = true;
            }
        },

        finishShopping() {
            if (this.yourInformation.name && this.yourInformation.whatsapp) {
                let message = `Hola, soy ${this.yourInformation.name}
quiero hacer este pedido en Agua Marina:
========================
`;

                this.shoppingCart.forEach(product => {
                    message += `
- ${product.units} ${product.Product} ${product.Barcode} / ${this.formatCurrency(product.Current_Price)}`;
                });

                message += `
========================
Total: ${this.formatCurrency(this.subtotal)}
========================
Mi información:
Nombre: ${this.yourInformation.name}
Celular: ${this.yourInformation.whatsapp}`;

                this.shoppingCart = [];
                this.saveToLocalStorage(this.shoppingCart);
                this.openCompleteYourInfo = false;

                this.redirectToWhatsApp(`57${this.companyWhatsapp}`, message)
            } else {
                alert('Por favor, completa toda la información requerida.')
            }
        },

        formatCurrency(value) {
            const numberValue = Number(value);
            if (isNaN(numberValue)) {
                return value; // Mantener el valor sin formatear si no es un número válido
            }
            return new Intl.NumberFormat('es-CO', {
                style: 'currency', currency: 'COP', minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(numberValue);
        },

        redirectToWhatsApp(phoneNumber, message) {
            const url = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`
            window.location.href = url
        }
    },
});
