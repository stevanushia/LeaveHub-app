<template>
  <div class="layout-container">
    <aside class="sidebar">
      <div class="brand">
        <h2>Leave<span class="text-blue">Hub</span></h2>
        <p>Leave Request Management</p>
      </div>

      <div class="menu-section">
        <p class="menu-label">ADMIN MENU</p>
        <ul class="menu-list">
          <li>
            <a href="#" @click.prevent="handleLogout" class="menu-item">
              <span class="icon">🚪</span> Logout
            </a>
          </li>
          <li>
            <a href="#" class="menu-item">
              <span class="icon">👥</span> Kelola User
            </a>
          </li>
          <li>
            <a href="#" class="menu-item active">
              <span class="icon">📋</span> Leave Requests
            </a>
          </li>
        </ul>
      </div>

      <div class="user-profile">
        <div class="avatar">S</div>
        <div class="user-info">
          <p class="name">Stevanus Hia</p>
          <p class="role">Admin</p>
        </div>
      </div>
    </aside>

    <main class="main-content">
      <slot></slot>
    </main>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const handleLogout = () => {
  // Sementara hapus token langsung untuk testing
  authStore.logout();
  router.push('/login');
};
</script>

<style scoped>
.layout-container { display: flex; min-height: 100vh; background-color: #f8fafc; font-family: sans-serif; }
.sidebar { width: 250px; background-color: #1a202c; color: white; display: flex; flex-direction: column; padding: 1.5rem 1rem; }
.brand h2 { margin: 0; font-size: 1.5rem; color: white; }
.brand .text-blue { color: #3b82f6; }
.brand p { margin: 0.25rem 0 2rem 0; font-size: 0.75rem; color: #94a3b8; }
.menu-label { font-size: 0.75rem; color: #64748b; font-weight: bold; margin-bottom: 1rem; letter-spacing: 1px; }
.menu-list { list-style: none; padding: 0; margin: 0; flex-grow: 1; }
.menu-item { display: flex; align-items: center; padding: 0.75rem 1rem; color: #cbd5e1; text-decoration: none; border-radius: 8px; margin-bottom: 0.5rem; transition: 0.2s; font-size: 0.9rem; }
.menu-item:hover, .menu-item.active { background-color: #3b82f6; color: white; }
.menu-item .icon { margin-right: 0.75rem; font-size: 1.1rem; }
.user-profile { display: flex; align-items: center; margin-top: auto; padding-top: 1rem; border-top: 1px solid #334155; }
.avatar { width: 35px; height: 35px; border-radius: 50%; background-color: #3b82f6; display: flex; justify-content: center; align-items: center; font-weight: bold; margin-right: 1rem; }
.user-info .name { margin: 0; font-size: 0.9rem; font-weight: bold; }
.user-info .role { margin: 0; font-size: 0.75rem; color: #94a3b8; }
.main-content { flex: 1; overflow-y: auto; }
</style>
