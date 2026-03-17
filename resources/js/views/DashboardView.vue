<template>
  <AppLayout>
    <div class="dashboard-content">
      <div class="header-actions">
        <div>
          <h1 class="page-title">Semua Leave Request</h1>
          <p class="page-subtitle">Kelola dan respons permohonan cuti dari semua user.</p>
        </div>
      </div>

      <div class="summary-cards">
        <div class="card">
          <div class="icon-wrap text-yellow">🏆</div>
          <div class="card-info">
            <h3>Pending</h3>
            <p class="count text-yellow">{{ countPending }}</p>
            <span class="desc">Menunggu keputusan</span>
          </div>
        </div>
        <div class="card">
          <div class="icon-wrap text-green">✓</div>
          <div class="card-info">
            <h3>Approved</h3>
            <p class="count text-green">{{ countApproved }}</p>
            <span class="desc">Disetujui</span>
          </div>
        </div>
        <div class="card">
          <div class="icon-wrap text-red">✗</div>
          <div class="card-info">
            <h3>Rejected</h3>
            <p class="count text-red">{{ countRejected }}</p>
            <span class="desc">Ditolak</span>
          </div>
        </div>
      </div>

      <div class="table-container">
        <h3 class="section-title">Perlu Tindakan (Pending)</h3>
        <table class="data-table">
          <thead>
            <tr>
              <th>USER</th>
              <th>TIPE</th>
              <th>TANGGAL</th>
              <th>HARI</th>
              <th>ALASAN</th>
              <th>STATUS</th>
              <th>AKSI</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="pendingRequests.length === 0">
              <td colspan="7" class="text-center" style="padding: 2rem; color: #94a3b8;">
                Tidak ada request cuti yang perlu tindakan.
              </td>
            </tr>
            <tr v-for="req in pendingRequests" :key="req.id">
              <td>{{ req.user?.name }}</td>
              <td>{{ req.leave_type?.name }}</td>
              <td>{{ formatDate(req.start_date) }} - {{ formatDate(req.end_date) }}</td>
              <td>{{ req.total_days }}</td>
              <td>{{ req.reason }}</td>
              <td><span class="badge badge-warning">{{ req.status }}</span></td>
              <td>
                <button @click="openActionForm(req, 'approve')" class="btn-action btn-approve">Approve</button>
                <button @click="openActionForm(req, 'reject')" class="btn-action btn-reject">Reject</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="selectedRequest" class="action-forms mt-4">

        <div class="action-card" :class="actionType === 'approve' ? 'border-green' : 'border-red'">
          <h4>
            {{ actionType === 'approve' ? 'Approve' : 'Reject' }} Request — {{ selectedRequest.user?.name }}
          </h4>
          <p>
            <strong>{{ selectedRequest.leave_type?.name }}</strong> ·
            {{ formatDate(selectedRequest.start_date) }} - {{ formatDate(selectedRequest.end_date) }}
            ({{ selectedRequest.total_days }} hari)
          </p>
          <p class="reason mb-3">Alasan: {{ selectedRequest.reason }}</p>

          <div class="form-group">
            <label>Catatan Admin (opsional)</label>
            <input
              type="text"
              v-model="adminNotes"
              :placeholder="actionType === 'approve' ? 'Approved, selamat berlibur.' : 'Alasan penolakan...'"
              class="input-field"
            />
          </div>

          <div v-if="errorMessage" class="error-message mt-2 mb-2">
            {{ errorMessage }}
          </div>

          <div class="form-actions mt-3">
            <button
              @click="submitAction"
              class="btn-action"
              :class="actionType === 'approve' ? 'btn-approve' : 'btn-reject'"
              :disabled="isProcessing"
            >
              {{ isProcessing ? 'Memproses...' : (actionType === 'approve' ? 'Konfirmasi Approve' : 'Konfirmasi Reject') }}
            </button>
            <button @click="closeActionForm" class="btn-action btn-cancel" :disabled="isProcessing">Batal</button>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import AppLayout from '../components/AppLayout.vue';
import api from '../services/api';

// State Management
const leaveRequests = ref<any[]>([]);
const isLoading = ref(false);
const isProcessing = ref(false);

// Action Form State
const selectedRequest = ref<any | null>(null);
const actionType = ref<'approve' | 'reject' | null>(null);
const adminNotes = ref('');
const errorMessage = ref<string | null>(null);

// Mengambil data dari API
const fetchRequests = async () => {
  isLoading.value = true;
  try {
    const response = await api.get('/leave-requests');
    leaveRequests.value = response.data.data;
  } catch (error) {
    console.error('Gagal memuat data leave requests', error);
  } finally {
    isLoading.value = false;
  }
};

// Computed Properties untuk Summary & Table
const pendingRequests = computed(() => leaveRequests.value.filter(req => req.status === 'pending'));
const countPending = computed(() => pendingRequests.value.length);
const countApproved = computed(() => leaveRequests.value.filter(req => req.status === 'approved').length);
const countRejected = computed(() => leaveRequests.value.filter(req => req.status === 'rejected').length);

// Fungsi untuk membuka form Approve/Reject di bawah tabel
const openActionForm = (request: any, type: 'approve' | 'reject') => {
  selectedRequest.value = request;
  actionType.value = type;
  adminNotes.value = '';
  errorMessage.value = null;

  // Scroll mulus ke form action (opsional, meningkatkan UX)
  setTimeout(() => {
    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
  }, 100);
};

const closeActionForm = () => {
  selectedRequest.value = null;
  actionType.value = null;
  adminNotes.value = '';
  errorMessage.value = null;
};

// Eksekusi API Approve / Reject
const submitAction = async () => {
  if (!selectedRequest.value || !actionType.value) return;

  isProcessing.value = true;
  errorMessage.value = null;

  try {
    const endpoint = `/leave-requests/${selectedRequest.value.id}/${actionType.value}`;

    await api.post(endpoint, {
      admin_notes: adminNotes.value
    });

    alert(`Request berhasil di-${actionType.value}!`);

    closeActionForm();
    fetchRequests(); // Refresh data tabel dan summary

  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || error.response?.data?.errors?.general?.[0] || 'Terjadi kesalahan sistem.';
  } finally {
    isProcessing.value = false;
  }
};

// Helper: Format Tanggal (Misal: 2026-03-20 -> 20 Mar 2026)
const formatDate = (dateString: string) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const options: Intl.DateTimeFormatOptions = { day: 'numeric', month: 'short', year: 'numeric' };
  return date.toLocaleDateString('id-ID', options);
};

// Panggil fetchRequests saat komponen pertama kali dimuat
onMounted(() => {
  fetchRequests();
});
</script>

<style scoped>
/* Semua styling CSS sama seperti sebelumnya, dengan tambahan utilitas margin (mt-2, mb-3 dll) */
.dashboard-content { padding: 2rem; }
.header-actions { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; }
.page-title { margin: 0; font-size: 1.5rem; color: #0f172a; }
.page-subtitle { margin: 0.25rem 0 0 0; color: #64748b; font-size: 0.9rem; }
.role-toggle { display: flex; align-items: center; background: #1e293b; padding: 0.25rem; border-radius: 8px; }
.role-toggle .label { color: #94a3b8; font-size: 0.8rem; margin: 0 0.5rem; }
.btn-toggle { background: transparent; border: none; color: white; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer; font-size: 0.85rem; }
.btn-toggle.active { background: #3b82f6; }

.summary-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
.card { background: white; padding: 1.5rem; border-radius: 12px; display: flex; align-items: flex-start; gap: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.icon-wrap { font-size: 1.5rem; }
.card-info h3 { margin: 0 0 0.5rem 0; font-size: 0.9rem; color: #64748b; font-weight: normal; }
.card-info .count { margin: 0; font-size: 2rem; font-weight: bold; }
.card-info .desc { font-size: 0.75rem; color: #94a3b8; }
.text-yellow { color: #eab308; }
.text-green { color: #22c55e; }
.text-red { color: #ef4444; }

.table-container { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 2rem; }
.section-title { margin: 0 0 1rem 0; font-size: 1rem; color: #0f172a; }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem; }
.data-table th { padding: 1rem; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.75rem; text-transform: uppercase; }
.data-table td { padding: 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; }
.badge { padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: bold; }
.badge-warning { background: #fef08a; color: #854d0e; }

.btn-action { padding: 0.4rem 0.75rem; border: none; border-radius: 6px; font-size: 0.8rem; cursor: pointer; color: white; margin-right: 0.5rem; font-weight: bold; }
.btn-approve { background: #22c55e; }
.btn-reject { background: #ef4444; }
.btn-cancel { background: white; color: #64748b; border: 1px solid #cbd5e1; }

.action-forms { display: flex; flex-direction: column; gap: 1.5rem; }
.action-card { background: white; padding: 1.5rem; border-radius: 12px; border-left: 4px solid; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.border-green { border-left-color: #22c55e; }
.border-red { border-left-color: #ef4444; }
.action-card h4 { margin: 0 0 0.5rem 0; font-size: 1rem; color: #0f172a; }
.action-card p { margin: 0 0 0.25rem 0; font-size: 0.9rem; color: #475569; }
.form-group { margin: 1rem 0; display: flex; flex-direction: column; gap: 0.5rem; }
.form-group label { font-size: 0.8rem; color: #64748b; }
.input-field { padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; outline: none; }
.form-actions { display: flex; gap: 0.5rem; }
.error-message { color: #ef4444; font-size: 0.85rem; background: #fee2e2; padding: 0.5rem; border-radius: 6px; }

/* Utilities */
.mt-2 { margin-top: 0.5rem; }
.mt-3 { margin-top: 1rem; }
.mt-4 { margin-top: 1.5rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mb-3 { margin-bottom: 1rem; }
</style>
