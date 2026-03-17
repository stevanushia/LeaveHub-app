<template>
  <AppLayout>
    <div class="dashboard-content">
      <div class="header-actions">
        <div>
          <h1 class="page-title">Ajukan Cuti</h1>
          <p class="page-subtitle">Isi form untuk mengajukan permohonan cuti baru.</p>
        </div>
      </div>

      <div class="form-container-main">
        <div class="form-card">
          <h3 class="section-title">Form Pengajuan Cuti</h3>
          <form @submit.prevent="submitLeave">
            <div class="form-group">
              <label>Jenis Cuti</label>
              <select v-model="form.leave_type_id" class="input-field" required>
                <option value="" disabled>Pilih Jenis Cuti...</option>
                <option v-for="balance in balances" :key="balance.leave_type_id" :value="balance.leave_type_id">
                  {{ balance.leave_type.name }} (Sisa: {{ balance.total_quota - balance.used }} hari)
                </option>
              </select>
            </div>

            <div class="form-row">
              <div class="form-group half">
                <label>Tanggal Mulai</label>
                <input type="date" v-model="form.start_date" class="input-field" :min="today" required />
              </div>
              <div class="form-group half">
                <label>Tanggal Selesai</label>
                <input type="date" v-model="form.end_date" class="input-field" :min="form.start_date || today" required />
              </div>
            </div>

            <div v-if="form.start_date && form.end_date" class="info-box-blue mb-3">
              📅 <strong>Total: {{ calculatedDays }} hari</strong> ({{ formatDate(form.start_date) }} - {{ formatDate(form.end_date) }})<br>
              Sisa balance setelah approved: {{ projectedBalance }} hari
            </div>

            <div class="form-group">
              <label>Alasan</label>
              <textarea v-model="form.reason" class="input-field" rows="3" placeholder="Liburan keluarga ke Malang" required></textarea>
            </div>

            <div v-if="apiError" class="error-message mt-2 mb-3">
              {{ apiError }}
            </div>

            <button type="submit" class="btn-action btn-primary mt-2" :disabled="isSubmitting || calculatedDays <= 0">
              {{ isSubmitting ? 'Memproses...' : 'Submit Pengajuan' }}
            </button>
          </form>
        </div>

        <div class="validation-sidebar">
          <div class="validation-card">
            <h4>Validasi yang Dicek</h4>
            <ul>
              <li><span class="text-green">✅</span> Tanggal valid (start ≤ end, tidak di masa lalu)</li>
              <li><span class="text-green">✅</span> Kuota cukup (sisa balance ≥ hari yang diajukan)</li>
              <li><span class="text-green">✅</span> Tidak ada overlap dengan request pending/approved</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import AppLayout from '../components/AppLayout.vue';
import api from '../services/api';

const router = useRouter();
const balances = ref<any[]>([]);
const isSubmitting = ref(false);
const apiError = ref<string | null>(null);

const today = new Date().toISOString().split('T')[0];

const form = ref({
  leave_type_id: '',
  start_date: '',
  end_date: '',
  reason: ''
});

// Ambil data sisa cuti user ini
const fetchBalances = async () => {
  try {
    const res = await api.get('/my-balances');
    balances.value = res.data;
  } catch (error) {
    console.error('Gagal memuat balance');
  }
};

// Hitung hari secara reaktif
const calculatedDays = computed(() => {
  if (!form.value.start_date || !form.value.end_date) return 0;
  const start = new Date(form.value.start_date);
  const end = new Date(form.value.end_date);
  const diffTime = end.getTime() - start.getTime();
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays >= 0 ? diffDays + 1 : 0;
});

// Hitung sisa balance proyeksi
const projectedBalance = computed(() => {
  if (!form.value.leave_type_id || calculatedDays.value <= 0) return '-';
  const selected = balances.value.find(b => b.leave_type_id === form.value.leave_type_id);
  if (!selected) return '-';
  const remaining = selected.total_quota - selected.used;
  return `${remaining} → ${remaining - calculatedDays.value}`;
});

const submitLeave = async () => {
  isSubmitting.value = true;
  apiError.value = null;

  try {
    await api.post('/leave-requests', form.value);
    alert('Pengajuan cuti berhasil dikirim!');
    // Reset form
    form.value.start_date = '';
    form.value.end_date = '';
    form.value.reason = '';

    // Kembali ke dashboard user (atau history)
    router.push('/dashboard');
  } catch (error: any) {
    if (error.response?.data?.errors) {
      // Mengambil pesan error pertama dari validasi (bisa overlap, quota, atau tanggal)
      const firstErrorKey = Object.keys(error.response.data.errors)[0];
      apiError.value = error.response.data.errors[firstErrorKey][0];
    } else {
      apiError.value = error.response?.data?.message || 'Terjadi kesalahan sistem.';
    }
  } finally {
    isSubmitting.value = false;
  }
};

const formatDate = (dateString: string) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

onMounted(() => {
  fetchBalances();
});
</script>

<style scoped>
/* Styling menyesuaikan Dashboard sebelumnya */
.dashboard-content { padding: 2rem; }
.header-actions { margin-bottom: 2rem; }
.page-title { margin: 0; font-size: 1.5rem; color: #0f172a; }
.page-subtitle { margin: 0.25rem 0 0 0; color: #64748b; font-size: 0.9rem; }

.form-container-main { display: flex; gap: 2rem; align-items: flex-start; }
.form-card { flex: 2; background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.validation-sidebar { flex: 1; display: flex; flex-direction: column; gap: 1rem; }

.validation-card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.validation-card h4 { margin: 0 0 1rem 0; font-size: 0.9rem; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem; }
.validation-card ul { list-style: none; padding: 0; margin: 0; font-size: 0.8rem; color: #475569; display: flex; flex-direction: column; gap: 0.75rem; }

.section-title { margin: 0 0 1.5rem 0; font-size: 1.1rem; color: #0f172a; }

.form-group { margin-bottom: 1.25rem; display: flex; flex-direction: column; gap: 0.5rem; }
.form-row { display: flex; gap: 1.5rem; }
.half { flex: 1; }
label { font-size: 0.85rem; font-weight: 600; color: #475569; }
.input-field { padding: 0.75rem 1rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; outline: none; background: #f8fafc; }
.input-field:focus { border-color: #3b82f6; background: white; }

.info-box-blue { background: #eff6ff; color: #1e3a8a; padding: 1rem; border-radius: 8px; font-size: 0.85rem; line-height: 1.5; }
.error-message { color: #b91c1c; font-size: 0.85rem; background: #fef2f2; padding: 0.75rem; border-radius: 8px; border: 1px solid #fecaca; }

.btn-action { padding: 0.75rem 1.5rem; border: none; border-radius: 8px; font-size: 0.95rem; cursor: pointer; font-weight: bold; transition: 0.2s; }
.btn-primary { background: #4f46e5; color: white; width: 100%; max-width: 200px; }
.btn-primary:hover { background: #4338ca; }
.btn-primary:disabled { background: #a5b4fc; cursor: not-allowed; }

.text-green { color: #22c55e; }
.mb-3 { margin-bottom: 1rem; }
.mt-2 { margin-top: 0.5rem; }
</style>
