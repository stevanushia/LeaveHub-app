import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '../views/LoginView.vue';
import DashboardView from '../views/DashboardView.vue'; // Import komponen
import UserManagementView from '../views/UserManagementView.vue'; // <-- Pastikan baris ini ada
import { useAuthStore } from '../stores/auth';
import SubmitLeaveView from '../views/SubmitLeaveView.vue';
import SisaKuotaView from '../views/SisaKuotaView.vue';
import RiwayatCutiView from '../views/RiwayatCutiView.vue';

const routes = [
    {
        path: '/',
        redirect: '/dashboard' // Default redirect ke dashboard
    },
    {
        path: '/login',
        name: 'Login',
        component: LoginView,
        meta: { guest: true }
    },
    {
        path: '/dashboard',
        name: 'Dashboard',
        component: DashboardView, // Daftarkan rute dashboard
        meta: { requiresAuth: true }
    },
    {
        path: '/users',
        name: 'UserManagement',
        component: UserManagementView,
        meta: { requiresAuth: true }
    },
    {
        path: '/ajukan-cuti',
        name: 'SubmitLeave',
        component: SubmitLeaveView,
        meta: { requiresAuth: true }
    },
    {
        path: '/sisa-kuota',
        name: 'SisaKuota',
        component: SisaKuotaView,
        meta: { requiresAuth: true }
    },
    {
        path: '/riwayat-cuti',
        name: 'RiwayatCuti',
        component: RiwayatCutiView,
        meta: { requiresAuth: true }
    },
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
