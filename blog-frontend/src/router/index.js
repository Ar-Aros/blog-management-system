import { createRouter, createWebHistory } from 'vue-router';

// We will create these components in the next step
import Login from '../views/Login.vue';
import Register from '../views/Register.vue';
import Home from '../views/Home.vue';
import ForgotPassword from '../views/ForgotPassword.vue';
import ResetPassword from '../views/ResetPassword.vue';
import Bookmarks from '../views/Bookmarks.vue';

const routes = [
    { path: '/', name: 'Home', component: Home },
    { path: '/login', name: 'Login', component: Login },
    { path: '/register', name: 'Register', component: Register },
    { path: '/forgot-password', component: ForgotPassword },
    { path: '/reset-password', component: ResetPassword },
    { path: '/bookmarks', name: 'bookmarks', component: () => import('../views/Bookmarks.vue') },
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

// Guard: Redirect guest users to dashboard if not logged in
router.beforeEach((to, from, next) => {
    // Add '/' to this list so guests can visit Home
    const publicPages = ['/login', '/register', '/', '/forgot-password', '/reset-password']; 
    const authRequired = !publicPages.includes(to.path);
    const loggedIn = localStorage.getItem('token');

    if (authRequired && !loggedIn) {
        next('/login');
    } else {
        next();
    }
});

export default router;