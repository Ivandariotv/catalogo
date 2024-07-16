import './bootstrap';

import { createApp } from 'vue';
import { createPinia } from "pinia";

import app from './pages/App.vue'

import router from './router/index.js'

createApp(app).use(router).use(createPinia()).mount('#app');
