import { defineStore } from 'pinia';
import api from '../services/api';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('auth_token') || null,
        adminToken: localStorage.getItem('admin_token') || null,
        // Menyimpan data user & admin yang sedang login
        user: JSON.parse(localStorage.getItem('user_data') || 'null'),
        adminUser: JSON.parse(localStorage.getItem('admin_user_data') || 'null'),
    }),
    getters: {
        isAuthenticated: (state) => !!state.token,
        isImpersonating: (state) => !!state.adminToken,
        role: (state) => state.user?.role || null, // Mendapatkan role aktif
    },
    actions: {
        setToken(token: string, userData: any) {
            this.token = token;
            this.user = userData;
            localStorage.setItem('auth_token', token);
            localStorage.setItem('user_data', JSON.stringify(userData));
            api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        },
        impersonate(userToken: string, userData: any) {
            // Simpan sesi admin ke cadangan
            this.adminToken = this.token;
            this.adminUser = this.user;
            localStorage.setItem('admin_token', this.token as string);
            localStorage.setItem('admin_user_data', JSON.stringify(this.user));

            // Masuk sebagai user
            this.setToken(userToken, userData);
        },
        revertImpersonate() {
            if (this.adminToken && this.adminUser) {
                // Kembalikan sesi admin
                this.setToken(this.adminToken, this.adminUser);
                this.adminToken = null;
                this.adminUser = null;
                localStorage.removeItem('admin_token');
                localStorage.removeItem('admin_user_data');
            }
        },
        logout() {
            this.token = null;
            this.adminToken = null;
            this.user = null;
            this.adminUser = null;
            localStorage.removeItem('auth_token');
            localStorage.removeItem('admin_token');
            localStorage.removeItem('user_data');
            localStorage.removeItem('admin_user_data');
        }
    }
});
