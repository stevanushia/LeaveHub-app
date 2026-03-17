<template>
  <div class="login-container">
    <div class="login-card">
      <div class="header">
        <h1>Leave<span class="text-blue">Hub</span></h1>
        <p class="subtitle">Leave Request Management System</p>
      </div>

      <form @submit.prevent="handleLogin" class="form-container">
        <div class="input-group">
          <label for="email">Email</label>
          <input
            type="email"
            id="email"
            v-model="form.email"
            :class="{ 'is-invalid': errors.email }"
            placeholder="admin@energeek.id"
          />
          <span v-if="errors.email" class="error-text">{{ errors.email[0] }}</span>
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <input
            type="password"
            id="password"
            v-model="form.password"
            :class="{ 'is-invalid': errors.password }"
            placeholder="••••••••••"
          />
          <span v-if="errors.password" class="error-text">{{ errors.password[0] }}</span>
        </div>

        <div v-if="generalError" class="error-general">
          {{ generalError }}
        </div>

        <button type="submit" :disabled="isLoading" class="btn-login">
          {{ isLoading ? 'Memproses...' : 'Login' }}
        </button>
      </form>

      <div class="footer">
        <p>Sanctum PAT · No register endpoint</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import api from '../services/api';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  email: '',
  password: ''
});

const isLoading = ref<boolean>(false);
// Record untuk menyimpan error per-field dari Laravel
const errors = ref<Record<string, string[]>>({});
const generalError = ref<string | null>(null);

const handleLogin = async () => {
  isLoading.value = true;
  errors.value = {};
  generalError.value = null;

  try {
    const response = await api.post('/login', form);

    // Simpan token ke Pinia (otomatis tersimpan di localStorage juga)
    authStore.setToken(response.data.access_token);

    // Nanti diarahkan ke Dashboard
    // alert('Login Berhasil!');
    router.push('/dashboard');

  } catch (error: any) {
    if (error.response?.status === 422) {
      // Validasi gagal (misal: format email salah, kolom kosong)
      errors.value = error.response.data.errors;
    } else if (error.response?.status === 401) {
      // Unauthorized (Kredensial salah)
      generalError.value = error.response.data.message;
    } else {
      generalError.value = 'Terjadi kesalahan pada server. Silakan coba lagi.';
    }
  } finally {
    isLoading.value = false;
  }
};
</script>

<style scoped>
/* Struktur dasar sama seperti sebelumnya, dengan tambahan styling error */
.login-container { display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: #1a202c; font-family: sans-serif; }
.login-card { background-color: #ffffff; width: 100%; max-width: 400px; padding: 2.5rem 2rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
.header { margin-bottom: 2rem; }
.header h1 { font-size: 1.75rem; font-weight: 700; color: #1e293b; margin: 0; }
.header .text-blue { color: #3b82f6; }
.header .subtitle { font-size: 0.9rem; color: #64748b; margin-top: 0.25rem; }
.form-container { display: flex; flex-direction: column; gap: 1.25rem; }
.input-group { display: flex; flex-direction: column; gap: 0.5rem; }
.input-group label { font-size: 0.875rem; font-weight: 600; color: #475569; }
.input-group input { padding: 0.75rem 1rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 1rem; outline: none; transition: 0.2s; }
.input-group input:focus { border-color: #3b82f6; }

/* Tambahan styling untuk invalid input & text error */
.input-group input.is-invalid { border-color: #ef4444; background-color: #fef2f2; }
.error-text { color: #ef4444; font-size: 0.75rem; font-weight: 500; margin-top: -0.25rem; }
.error-general { color: #ef4444; font-size: 0.875rem; background-color: #fee2e2; padding: 0.75rem; border-radius: 6px; }

.btn-login { background-color: #3b82f6; color: #ffffff; padding: 0.75rem; border: none; border-radius: 6px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 0.5rem; transition: 0.2s; }
.btn-login:hover { background-color: #2563eb; }
.btn-login:disabled { background-color: #93c5fd; cursor: not-allowed; }
.footer { margin-top: 1.5rem; text-align: center; }
.footer p { font-size: 0.75rem; color: #94a3b8; }
</style>
