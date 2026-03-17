<template>
  <div class="layout-container">
    <aside class="sidebar">
      <div class="brand">
        <h2>Leave<span class="text-blue">Hub</span></h2>
        <p>Leave Request Management</p>
      </div>

      <div v-if="authStore.role === 'admin'" class="menu-section">
        <p class="menu-label">ADMIN MENU</p>
        <ul class="menu-list">
          <li>
            <router-link to="/users" class="menu-item" active-class="active">
              <span class="icon">👥</span> Kelola User
            </router-link>
          </li>
          <li>
            <router-link to="/dashboard" class="menu-item" active-class="active">
              <span class="icon">📋</span> Leave Requests
            </router-link>
          </li>
        </ul>
      </div>

      <div v-if="authStore.role === 'employee'" class="menu-section">
        <p class="menu-label">USER MENU</p>
        <ul class="menu-list">
          <li>
            <router-link to="/sisa-kuota" class="menu-item" active-class="active">
              <span class="icon">📊</span> Sisa Kuota
            </router-link>
          </li>
          <li>
            <router-link to="/ajukan-cuti" class="menu-item" active-class="active">
              <span class="icon">✏️</span> Ajukan Cuti
            </router-link>
          </li>
          <li>
            <router-link to="/riwayat-cuti" class="menu-item" active-class="active">
              <span class="icon">📁</span> Riwayat Cuti
            </router-link>
          </li>
        </ul>
      </div>

      <div class="bottom-section">

        <button v-if="authStore.isImpersonating" @click="handleRevert" class="btn-revert">
          ↩ Kembali ke Admin
        </button>

        <button @click="handleLogout" class="btn-logout">
          🚪 Logout
        </button>

        <div class="user-profile">
          <div class="avatar" :class="authStore.role === 'admin' ? 'bg-blue' : 'bg-green'">
            {{ userInitial }}
          </div>
          <div class="user-info">
            <p class="name">{{ authStore.user?.name || 'User' }}</p>
            <p class="email">{{ authStore.user?.email || '' }}</p>
          </div>
        </div>
      </div>
    </aside>

    <main class="main-content">
      <slot></slot>
    </main>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

// Mengambil inisial nama untuk Avatar (Misal "Budi Santoso" -> "B")
const userInitial = computed(() => {
  const name = authStore.user?.name || 'U';
  return name.charAt(0).toUpperCase();
});

const handleLogout = () => {
  authStore.logout();
  router.push('/login');
};

const handleRevert = () => {
  authStore.revertImpersonate();
  router.push('/users'); // Kembali ke halaman kelola user admin
};
</script>

<style scoped>
.layout-container { display: flex; min-height: 100vh; background-color: #f8fafc; font-family: sans-serif; }
.sidebar { width: 250px; background-color: #1a202c; color: white; display: flex; flex-direction: column; padding: 1.5rem 1rem; }
.brand h2 { margin: 0; font-size: 1.5rem; color: white; }
.brand .text-blue { color: #3b82f6; }
.brand p { margin: 0.25rem 0 2rem 0; font-size: 0.75rem; color: #94a3b8; }
.menu-section { flex-grow: 1; }
.menu-label { font-size: 0.75rem; color: #64748b; font-weight: bold; margin-bottom: 1rem; letter-spacing: 1px; }
.menu-list { list-style: none; padding: 0; margin: 0; }
.menu-item { display: flex; align-items: center; padding: 0.75rem 1rem; color: #cbd5e1; text-decoration: none; border-radius: 8px; margin-bottom: 0.5rem; transition: 0.2s; font-size: 0.9rem; }
.menu-item:hover, .menu-item.active { background-color: #3b82f6; color: white; }
.menu-item .icon { margin-right: 0.75rem; font-size: 1.1rem; }

/* Styling Area Bawah Sidebar */
.bottom-section { margin-top: auto; display: flex; flex-direction: column; gap: 0.5rem; }

.btn-logout { background: transparent; color: #cbd5e1; border: 1px solid #334155; padding: 0.5rem; border-radius: 6px; cursor: pointer; text-align: left; font-size: 0.85rem; transition: 0.2s; }
.btn-logout:hover { background: #334155; color: white; }

.btn-revert { background: #eab308; color: #854d0e; border: none; padding: 0.5rem; border-radius: 6px; cursor: pointer; text-align: left; font-size: 0.85rem; font-weight: bold; transition: 0.2s; }
.btn-revert:hover { background: #facc15; }

.user-profile { display: flex; align-items: center; padding-top: 1rem; border-top: 1px solid #334155; margin-top: 0.5rem; }
.avatar { width: 35px; height: 35px; border-radius: 50%; display: flex; justify-content: center; align-items: center; font-weight: bold; margin-right: 1rem; color: white;}
.bg-blue { background-color: #3b82f6; }
.bg-green { background-color: #10b981; }

.user-info .name { margin: 0; font-size: 0.9rem; font-weight: bold; }
.user-info .email { margin: 0; font-size: 0.7rem; color: #94a3b8; word-break: break-all; }
.main-content { flex: 1; overflow-y: auto; }
</style>
