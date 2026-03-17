<template>
  <AppLayout>
    <div class="dashboard-content">
      <div class="header-actions">
        <div>
          <h1 class="page-title">Riwayat Cuti Saya</h1>
          <p class="page-subtitle">Semua pengajuan cuti yang pernah disubmit.</p>
        </div>
      </div>

      <div class="table-container">
        <div class="table-header">
          <h3 class="section-title">Riwayat Pengajuan</h3>
          <span class="text-muted">{{ leaveRequests.length }} request</span>
        </div>

        <table class="data-table">
          <thead>
            <tr>
              <th>TIPE</th>
              <th>TANGGAL</th>
              <th>HARI</th>
              <th>ALASAN</th>
              <th>STATUS</th>
              <th>CATATAN ADMIN</th>
              <th>AKSI</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="isLoading">
              <td colspan="7" class="text-center" style="padding: 2rem;">Memuat data...</td>
            </tr>
            <tr v-else-if="leaveRequests.length === 0">
              <td colspan="7" class="text-center" style="padding: 2rem; color: #94a3b8;">
                Belum ada riwayat pengajuan cuti.
              </td>
            </tr>
            <tr v-for="req in leaveRequests" :key="req.id">
              <td>{{ req.leave_type?.name }}</td>
              <td>{{ formatDateRange(req.start_date, req.end_date) }}</td>
              <td>{{ req.total_days }}</td>
              <td>{{ req.reason }}</td>
              <td>
                <span class="badge" :class="getBadgeClass(req.status)">
                  {{ req.status }}
                </span>
              </td>
              <td>{{ req.admin_notes || '—' }}</td>
              <td>
                <button
                  v-if="req.status === 'pending'"
                  @click="openActionForm(req, 'cancel')"
                  class="btn-action btn-cancel-request"
                >
                  Cancel
                </button>
                <button
                  v-else-if="req.status === 'rejected' || req.status === 'cancelled'"
                  @click="openActionForm(req, 'delete')"
                  class="btn-action btn-delete-request"
                >
                  Hapus
                </button>
                <span v-else class="text-muted">—</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="selectedRequest" class="action-forms mt-4">
        <div class="action-card" :class="actionType === 'cancel' ? 'border-orange' : 'border-red'">
          <h4>{{ actionType === 'cancel' ? 'Cancel Request?' : 'Hapus Riwayat?' }}</h4>
          <p>
            <strong>{{ selectedRequest.leave_type?.name }}</strong> ·
            {{ formatDateRange(selectedRequest.start_date, selectedRequest.end_date) }}
            ({{ selectedRequest.total_days }} hari)
          </p>
          <p class="mb-3">Status saat ini: <span class="badge" :class="getBadgeClass(selectedRequest.status)">{{ selectedRequest.status }}</span></p>

          <p class="warning-text mb-3">
            {{ actionType === 'cancel'
               ? 'Request yang sudah di-cancel tidak bisa dikembalikan.'
               : 'Data ini akan dihapus dari daftar riwayat Anda.' }}
          </p>

          <div v-if="apiError" class="error-message mb-3">
            {{ apiError }}
          </div>

          <div class="form-actions">
            <button
              @click="submitAction"
              class="btn-action"
              :class="actionType === 'cancel' ? 'btn-cancel-request' : 'btn-delete-request-solid'"
              :disabled="isProcessing"
            >
              {{ isProcessing ? 'Memproses...' : (actionType === 'cancel' ? 'Ya, Cancel' : 'Ya, Hapus') }}
            </button>
            <button @click="closeActionForm" class="btn-action btn-outline" :disabled="isProcessing">Batal</button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import AppLayout from '../components/AppLayout.vue';
import api from '../services/api';

const leaveRequests = ref<any[]>([]);
const isLoading = ref(true);
const isProcessing = ref(false);
const apiError = ref<string | null>(null);

const selectedRequest = ref<any | null>(null);
const actionType = ref<'cancel' | 'delete' | null>(null);

const fetchHistory = async () => {
  isLoading.value = true;
  try {
    const res = await api.get('/leave-requests');
    leaveRequests.value = res.data.data;
  } catch (error) {
    console.error("Gagal memuat riwayat cuti", error);
  } finally {
    isLoading.value = false;
  }
};

const openActionForm = (request: any, type: 'cancel' | 'delete') => {
  selectedRequest.value = request;
  actionType.value = type;
  apiError.value = null;

  setTimeout(() => {
    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
  }, 100);
};

const closeActionForm = () => {
  selectedRequest.value = null;
  actionType.value = null;
  apiError.value = null;
};

const submitAction = async () => {
  if (!selectedRequest.value || !actionType.value) return;

  isProcessing.value = true;
  apiError.value = null;

  try {
    if (actionType.value === 'cancel') {
      await api.post(`/leave-requests/${selectedRequest.value.id}/cancel`);
      alert('Pengajuan cuti berhasil dibatalkan.');
    } else if (actionType.value === 'delete') {
      await api.delete(`/leave-requests/${selectedRequest.value.id}`);
      alert('Riwayat cuti berhasil dihapus.');
    }

    closeActionForm();
    fetchHistory();
  } catch (error: any) {
    apiError.value = error.response?.data?.message || 'Terjadi kesalahan sistem.';
  } finally {
    isProcessing.value = false;
  }
};

// Helper untuk format tanggal yang pintar (Misal: 20-22 Mar 2026 atau 5 Mar 2026)
const formatDateRange = (start: string, end: string) => {
  if (!start || !end) return '';
  const d1 = new Date(start);
  const d2 = new Date(end);

  const options: Intl.DateTimeFormatOptions = { day: 'numeric', month: 'short', year: 'numeric' };
  const str2 = d2.toLocaleDateString('id-ID', options);

  if (start === end) {
    return str2;
  }

  if (d1.getMonth() === d2.getMonth() && d1.getFullYear() === d2.getFullYear()) {
    return `${d1.getDate()} - ${str2}`;
  }

  const str1 = d1.toLocaleDateString('id-ID', options);
  return `${str1} - ${str2}`;
};

const getBadgeClass = (status: string) => {
  switch (status) {
    case 'pending': return 'badge-warning';
    case 'approved': return 'badge-success';
    case 'rejected': return 'badge-danger';
    case 'cancelled': return 'badge-secondary';
    default: return '';
  }
};

onMounted(() => {
  fetchHistory();
});
</script>

<style scoped>
.dashboard-content { padding: 2rem; }
.header-actions { margin-bottom: 2rem; }
.page-title { margin: 0; font-size: 1.5rem; color: #0f172a; }
.page-subtitle { margin: 0.25rem 0 0 0; color: #64748b; font-size: 0.9rem; }

.table-container { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
.section-title { margin: 0; font-size: 1rem; color: #0f172a; }
.text-muted { color: #94a3b8; font-size: 0.85rem; }

.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem; }
.data-table th { padding: 1rem; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem; text-transform: uppercase; }
.data-table td { padding: 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; }

.badge { padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: bold; }
.badge-warning { background: #fef08a; color: #854d0e; }
.badge-success { background: #bbf7d0; color: #166534; }
.badge-danger { background: #fecaca; color: #991b1b; }
.badge-secondary { background: #e2e8f0; color: #475569; }

.btn-action { padding: 0.4rem 0.75rem; border: none; border-radius: 6px; font-size: 0.8rem; cursor: pointer; font-weight: bold; transition: 0.2s; }
.btn-cancel-request { background: #ef4444; color: white; }
.btn-delete-request { background: transparent; color: #ef4444; border: 1px solid #fca5a5; }
.btn-delete-request-solid { background: #dc2626; color: white; }
.btn-outline { background: white; color: #64748b; border: 1px solid #cbd5e1; }

.action-forms { max-width: 500px; }
.action-card { background: white; padding: 1.5rem; border-radius: 12px; border-left: 4px solid; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.border-orange { border-left-color: #f97316; }
.border-red { border-left-color: #ef4444; }
.action-card h4 { margin: 0 0 0.5rem 0; font-size: 1rem; color: #0f172a; }
.action-card p { margin: 0 0 0.25rem 0; font-size: 0.9rem; color: #475569; }

.warning-text { color: #64748b; font-size: 0.85rem; }
.error-message { color: #ef4444; font-size: 0.85rem; background: #fee2e2; padding: 0.5rem; border-radius: 6px; }

.form-actions { display: flex; gap: 0.5rem; }
.mt-4 { margin-top: 1.5rem; }
.mb-3 { margin-bottom: 1rem; }
</style>
