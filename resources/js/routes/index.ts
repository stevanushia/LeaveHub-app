import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '../views/LoginView.vue';
import { useAuthStore } from '../stores/auth';

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: LoginView,
        meta: { guest: true }
    },
    // Placeholder untuk halaman dashboard nanti
    // {
    //     path: '/',
    //     name: 'Dashboard',
    //     component: () => import('../views/DashboardView.vue'),
    //     meta: { requiresAuth: true }
    // }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Navigation Guard
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'Login' });
    } else if (to.meta.guest && authStore.isAuthenticated) {
        next({ name: 'Dashboard' }); // Redirect jika sudah login tapi akses /login
    } else {
        next();
    }
});

export default router;
