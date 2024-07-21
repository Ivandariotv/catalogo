import { createRouter, createWebHistory } from "vue-router";

import home from '../pages/Home.vue'
import notFound from '../pages/NotFound.vue'

const routes = [
    {
        path: '/',
        component: home
    },
    {
        path: '/categories/:categoryId',
        component: home,
        props: true
    },
    {
        path: '/:pathMatch(.*)*',
        component: notFound
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router
