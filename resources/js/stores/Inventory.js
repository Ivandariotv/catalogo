import { defineStore } from "pinia";
import { ref } from "vue";
import axios from "axios";
import Product from "../pages/Product.vue";
import { useRouter } from 'vue-router';


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
        router: useRouter(),
        banner: [],
    }),

    getters: {
        subtotal: (state) => {
            // Asegúrate de que state.shoppingCart esté definido antes de proceder
            if (!state.shoppingCart) return 0;

            // Calcula el número total de productos directamente
            const totalNumberProducts = state.shoppingCart.reduce((total, product) => {
                return total + product.units;
            }, 0);

            // Verifica si hay al menos 3 productos
            if (totalNumberProducts >= 3) {
                return state.shoppingCart.reduce((total, product) => {
                    return total + (product.Price_Wholesale * product.units);
                }, 0);
            } else {
                return state.shoppingCart.reduce((total, product) => {
                    return total + (product.Current_Price * product.units);
                }, 0);
            }

            return 0; // Devuelve 0 si no hay al menos 3 productos
        },

        totalNumberProducts: (state) => {
            return state.shoppingCart.reduce((total, product) => {
                return total + product.units;
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

        /** Obtiene whatsapp de la empresa */
        async getBanners() {
            axios({
                method: "get",
                url: "/api/Banners",
            }).then(({ data }) => {
                this.banner = data;
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
            this.router.push({ path: '/' });
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
                this.selectedImage = data.UrlServerImage + data.product_images[0]?.name;
                this.loadingProduct = false;
            });
        },

        addToShoppingCart(product, color, size) {
            let units = 0;

            // Buscar las unidades disponibles para el color y tamaño seleccionado
            if (product.color_size) {
                Object.entries(product.color_size).forEach(([key, value]) => {
                    if (value.color_id === color.color_id && value.size_id === size.size_id) {
                        units = value.units;
                    }
                });
            }

            // Si no hay unidades disponibles, no se añade el producto
            if (units <= 0) return false;

            this.openShoppingCart = true;

            // Buscar el producto en el carrito por su ID, color_id y size_id
            const existingProductIndex = this.shoppingCart.findIndex(item =>
                item.Id === product.Id &&
                item.color?.color_id === color.color_id &&
                item.size?.size_id === size.size_id
            );

            // Verifica si el producto ya existe en el carrito
            if (existingProductIndex !== -1) {
                // Incrementar las unidades del producto existente
                this.shoppingCart[existingProductIndex].units += 1;
            } else {
                // Si el producto no existe en el carrito, agregarlo con una unidad
                let productNew = { ...product }; // Crear una copia del producto
                productNew.units = 1;
                productNew.color = color;
                productNew.size = size;
                this.shoppingCart = [...this.shoppingCart, productNew]; // Añadir el nuevo producto al carrito
            }

            // Guardar el carrito actualizado en el localStorage
            this.saveToLocalStorage(this.shoppingCart);
        },


        removeFromShoppingCart(product, color, size) {
            const existingProductIndex = this.shoppingCart.findIndex(item =>
                item.Id === product.Id &&
                item.color?.color_id === color.color_id &&
                item.size?.size_id === size.size_id
            );

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

        removeItemFromShoppingCart(product, color, size) {
            const existingProductIndex = this.shoppingCart.findIndex(item =>
                item.Id === product.Id &&
                item.color?.color_id === color.color_id &&
                item.size?.size_id === size.size_id
            );

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
- ${product.units} ${product.Product} - ${product.color.color_name} (${product.size.size}) ${product.Barcode} / ${this.formatCurrency(product.Current_Price)}`;
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
