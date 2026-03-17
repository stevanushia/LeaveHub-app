import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('auth_token') || null,
        user: null,
    }),
    getters: {
        isAuthenticated: (state) => !!state.token,
    },
    actions: {
        setToken(token: string) {
            this.token = token;
            localStorage.setItem('auth_token', token);
        },
        logout() {
            this.token = null;
            this.user = null;
            localStorage.removeItem('auth_token');
        }
    }
});
