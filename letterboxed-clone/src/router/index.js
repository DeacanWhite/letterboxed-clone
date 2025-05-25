import { createRouter, createWebHashHistory } from 'vue-router'
import Home from '../views/Home.vue'
import News from '../views/News.vue'
import About from '../views/About.vue'
import Browse from '../views/Browse.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import Dashboard from '../views/Dashboard.vue'
import Watchlist from '../views/Watchlist.vue'
import Details from '../views/Details.vue'

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
    },
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { hideNavigation: true } // Hide navigation on login page
    },
    {
        path: '/register',
        name: 'Register',
        component: Register,
        meta: { hideNavigation: true } // Hide navigation on register page
    },
    {
        path: '/dashboard',
        name: 'Dashboard',
        component: Dashboard,
        meta: { requiresAuth: true } // Require authentication for dashboard
    },
    {
        path: '/watchlist',
        name: 'Watchlist',
        component: Watchlist,
        meta: { requiresAuth: true } // Require authentication for watchlist
    },
    {
        path: '/details/:type/:id',
        name: 'Details',
        component: Details,
        props: true // Pass route params as props to component
    }
];

const router = createRouter({
    history: createWebHashHistory('/cos30043/s103994779/A4/'),
    //history: createWebHistory(),
    routes,
})

// Protect routes that require authentication
router.beforeEach((to, from, next) => {
    const isAuthenticated = localStorage.getItem('isAuthenticated') === 'true';
    
    if (to.meta.requiresAuth && !isAuthenticated) {
        // If route requires auth and user is not authenticated, redirect to login
        next({ name: 'Login' });
    } else if ((to.name === 'Login' || to.name === 'Register') && isAuthenticated) {
        // If user is authenticated and trying to access login/register, redirect to dashboard
        next({ name: 'Dashboard' });
    } else {
        // Otherwise, proceed normally
        next();
    }
});

export default router