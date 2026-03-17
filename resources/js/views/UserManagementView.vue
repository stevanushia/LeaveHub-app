<template>
  <AppLayout>
    <div class="dashboard-content">
      <div class="header-actions">
        <div>
          <h1 class="page-title">Kelola User</h1>
          <p class="page-subtitle">Buat dan kelola akun user. Maksimal 2 user.</p>
        </div>
      </div>

      <div class="summary-cards">
        <div class="card">
          <div class="icon-wrap text-blue">👤</div>
          <div class="card-info">
            <h3>Total User</h3>
            <p class="count text-blue">{{ totalUsers }}</p>
            <span class="desc">Maks. 2 user</span>
          </div>
        </div>
        <div class="card">
          <div class="icon-wrap text-green">✓</div>
          <div class="card-info">
            <h3>Slot Tersedia</h3>
            <p class="count text-green">{{ 2 - totalUsers }}</p>
            <span class="desc">{{ totalUsers >= 2 ? 'Kuota penuh' : 'Tersedia' }}</span>
          </div>
        </div>
      </div>

      <div class="table-container">
        <div class="table-header">
          <h3 class="section-title">Daftar User</h3>
          <button
            @click="openModal('create')"
            class="btn-action btn-primary"
            :disabled="totalUsers >= 2"
          >
            + Tambah User
          </button>
        </div>

        <table class="data-table">
          <thead>
            <tr>
              <th>NAMA</th>
              <th>EMAIL</th>
              <th>ANNUAL LEAVE</th>
              <th>SICK LEAVE</th>
              <th>AKSI</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="users.length === 0">
              <td colspan="5" class="text-center">Belum ada user.</td>
            </tr>
            <tr v-for="user in users" :key="user.id">
              <td>{{ user.name }}</td>
              <td>{{ user.email }}</td>
              <td>
                {{ user.formatted_balances?.['Annual Leave']?.remaining ?? 0 }} /
                {{ user.formatted_balances?.['Annual Leave']?.total ?? 0 }} hari
              </td>
              <td>
                {{ user.formatted_balances?.['Sick Leave']?.remaining ?? 0 }} /
                {{ user.formatted_balances?.['Sick Leave']?.total ?? 0 }} hari
              </td>
              <td>
                <button @click="openModal('edit', user)" class="btn-action btn-outline">Update Password</button>
                <button @click="loginAsUser(user)" class="btn-action btn-primary" style="margin-left: 0.5rem;">Masuk sebagai User</button>
                </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="isModalOpen" class="modal-backdrop">
      <div class="modal-content">
        <button @click="closeModal" class="btn-close">×</button>
        <h3>{{ isEditMode ? 'Edit User' : 'Tambah User Baru' }}</h3>
        <p class="modal-subtitle" v-if="!isEditMode">Leave balance otomatis ter-assign saat user dibuat.</p>

        <div v-if="errors.general" class="error-general mb-3">
          {{ errors.general[0] }}
        </div>

        <form @submit.prevent="saveUser">
          <div class="form-group">
            <label>Nama Lengkap</label>
            <input type="text" v-model="form.name" class="input-field" :class="{ 'is-invalid': errors.name }" placeholder="Budi Santoso" />
            <span v-if="errors.name" class="error-text">{{ errors.name[0] }}</span>
          </div>

          <div class="form-row">
            <div class="form-group half">
              <label>Email</label>
              <input type="email" v-model="form.email" class="input-field" :class="{ 'is-invalid': errors.email }" placeholder="budi@energeek.id" />
              <span v-if="errors.email" class="error-text">{{ errors.email[0] }}</span>
            </div>
            <div class="form-group half">
              <label>Password <span v-if="isEditMode" style="font-weight:normal; font-size: 0.75rem;">(Opsional)</span></label>
              <input type="password" v-model="form.password" class="input-field" :class="{ 'is-invalid': errors.password }" placeholder="Min. 8 karakter" />
              <span v-if="errors.password" class="error-text">{{ errors.password[0] }}</span>
            </div>
          </div>

          <div v-if="!isEditMode" class="info-box">
            ℹ️ Auto-assign: Annual Leave (12 hari), Sick Leave (6 hari)
          </div>

          <div class="form-actions mt-4">
            <button type="submit" class="btn-action btn-primary w-100" :disabled="isLoading">
              {{ isLoading ? 'Menyimpan...' : 'Simpan User' }}
            </button>
            <button type="button" @click="closeModal" class="btn-action btn-cancel w-100">Batal</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue';
import AppLayout from '../components/AppLayout.vue';
import api from '../services/api';

import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
const router = useRouter();
const authStore = useAuthStore();

const users = ref<any[]>([]);
const totalUsers = ref(0);
const isLoading = ref(false);
const isModalOpen = ref(false);
const isEditMode = ref(false);
const editId = ref<number | null>(null);

const errors = ref<Record<string, string[]>>({});

const loginAsUser = async (user: any) => {
  if (confirm(`Anda akan masuk menggunakan akun ${user.name}. Lanjutkan?`)) {
    try {
      const res = await api.post(`/impersonate/${user.id}`);
      authStore.impersonate(res.data.access_token, res.data.user);
      alert(`Berhasil masuk sebagai ${user.name}`);
      router.push('/ajukan-cuti'); // Arahkan ke halaman form cuti
    } catch (e) {
      alert('Gagal berganti akun');
    }
  }
};

const form = reactive({
  name: '',
  email: '',
  password: ''
});

const fetchUsers = async () => {
  try {
    const response = await api.get('/users');
    users.value = response.data.users;
    totalUsers.value = response.data.total_users;
  } catch (error) {
    console.error("Gagal mengambil data user");
  }
};

const openModal = (mode: 'create' | 'edit', user: any = null) => {
  errors.value = {};
  isEditMode.value = mode === 'edit';

  if (isEditMode.value && user) {
    editId.value = user.id;
    form.name = user.name;
    form.email = user.email;
    form.password = ''; // Kosongkan agar tidak terisi hash
  } else {
    editId.value = null;
    form.name = '';
    form.email = '';
    form.password = '';
  }

  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const saveUser = async () => {
  isLoading.value = true;
  errors.value = {};

  try {
    if (isEditMode.value) {
      await api.put(`/users/${editId.value}`, form);
      alert('User berhasil diupdate!');
    } else {
      await api.post('/users', form);
      alert('User berhasil ditambahkan!');
    }

    closeModal();
    fetchUsers(); // Refresh data tabel
  } catch (error: any) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors;
    } else {
      alert('Terjadi kesalahan sistem.');
    }
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchUsers();
});


</script>

<style scoped>
/* Styling menyesuaikan Dashboard sebelumnya */
.dashboard-content { padding: 2rem; }
.header-actions { margin-bottom: 2rem; }
.page-title { margin: 0; font-size: 1.5rem; color: #0f172a; }
.page-subtitle { margin: 0.25rem 0 0 0; color: #64748b; font-size: 0.9rem; }

.summary-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
.card { background: white; padding: 1.5rem; border-radius: 12px; display: flex; align-items: flex-start; gap: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.icon-wrap { font-size: 1.5rem; }
.card-info h3 { margin: 0 0 0.5rem 0; font-size: 0.9rem; color: #64748b; font-weight: normal; }
.card-info .count { margin: 0; font-size: 2rem; font-weight: bold; }
.card-info .desc { font-size: 0.75rem; color: #94a3b8; }
.text-blue { color: #3b82f6; }
.text-green { color: #22c55e; }

.table-container { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.section-title { margin: 0; font-size: 1rem; color: #0f172a; }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem; }
.data-table th { padding: 1rem; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem; text-transform: uppercase; }
.data-table td { padding: 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; }
.text-center { text-align: center; color: #94a3b8; }

.btn-action { padding: 0.5rem 1rem; border: none; border-radius: 6px; font-size: 0.85rem; cursor: pointer; font-weight: bold; transition: 0.2s; }
.btn-primary { background: #4f46e5; color: white; }
.btn-primary:hover { background: #4338ca; }
.btn-primary:disabled { background: #a5b4fc; cursor: not-allowed; }
.btn-outline { background: white; color: #475569; border: 1px solid #cbd5e1; }
.btn-outline:hover { background: #f8fafc; }
.btn-cancel { background: #f1f5f9; color: #475569; border: none; }

/* Form & Modal Styling */
.modal-backdrop { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(15, 23, 42, 0.6); display: flex; justify-content: center; align-items: center; z-index: 50; }
.modal-content { background: white; padding: 2rem; border-radius: 12px; width: 100%; max-width: 500px; position: relative; }
.btn-close { position: absolute; top: 1rem; right: 1rem; background: transparent; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b; }
.modal-content h3 { margin: 0 0 0.25rem 0; color: #0f172a; }
.modal-subtitle { font-size: 0.8rem; color: #64748b; margin-bottom: 1.5rem; }

.form-group { margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.25rem; }
.form-row { display: flex; gap: 1rem; }
.half { flex: 1; }
label { font-size: 0.8rem; font-weight: 600; color: #475569; }
.input-field { padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; outline: none; }
.input-field.is-invalid { border-color: #ef4444; background-color: #fef2f2; }
.error-text { color: #ef4444; font-size: 0.75rem; margin-top: 0.25rem; }
.error-general { color: #ef4444; font-size: 0.85rem; background: #fee2e2; padding: 0.75rem; border-radius: 6px; }

.info-box { background: #eff6ff; color: #1d4ed8; padding: 0.75rem; border-radius: 6px; font-size: 0.8rem; margin-top: 1rem; }
.form-actions { display: flex; gap: 0.5rem; }
.w-100 { flex: 1; }
.mt-4 { margin-top: 1.5rem; }
.mb-3 { margin-bottom: 1rem; }
</style>
