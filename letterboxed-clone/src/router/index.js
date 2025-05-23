import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import News from '../views/News.vue'
import About from '../views/About.vue'
import Browse from '../views/Browse.vue'

const routes = [
    {
        path: '/',
        name: 'Home',
        component: Home
    },
    {
        path: '/news',
        name: 'News',
        component: News
    },
    {
        path: '/about',
        name: 'About',
        component: About
    },
    {
        path: '/browse',
        name: 'Browse',
        component: Browse
    }
];

const router = createRouter({
    history: createWebHistory('/cos30043/s103994779/A3/'),
    //history: createWebHistory(),
    routes,
})

export default router