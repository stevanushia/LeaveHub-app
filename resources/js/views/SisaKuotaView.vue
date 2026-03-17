<template>
  <AppLayout>
    <div class="dashboard-content">
      <div class="header-actions">
        <div>
          <h1 class="page-title">Sisa Kuota Cuti</h1>
          <p class="page-subtitle">Periode: Tahun {{ currentYear }} · Kalkulasi: total_quota - used</p>
        </div>
      </div>

      <div v-if="isLoading" class="loading-state">
        Memuat data...
      </div>

      <div v-else>
        <div class="balance-cards">
          <div v-for="(balance, index) in balances" :key="balance.id" class="balance-card">
            <h3 class="balance-title">
              {{ getLeaveIcon(balance.leave_type.name) }} {{ balance.leave_type.name }}
            </h3>

            <div class="progress-container">
              <div
                class="progress-bar"
                :class="index % 2 === 0 ? 'bg-blue' : 'bg-green'"
                :style="{ width: getPercentage(balance.used, balance.total_quota) + '%' }"
              ></div>
            </div>

            <div class="balance-stats">
              <span class="used-text">Terpakai: <strong>{{ balance.used }} hari</strong></span>
              <span class="remain-text">
                Sisa: <strong>{{ balance.total_quota - balance.used }} / {{ balance.total_quota }} hari</strong>
              </span>
            </div>
          </div>
        </div>

        <div class="summary-cards mt-4">
          <div class="card">
            <div class="icon-wrap text-yellow">🏆</div>
            <div class="card-info">
              <h3>Pending</h3>
              <p class="count text-yellow">{{ countPending }}</p>
              <span class="desc">Menunggu approval</span>
            </div>
          </div>

          <div class="card">
            <div class="icon-wrap text-green">✓</div>
            <div class="card-info">
              <h3>Approved</h3>
              <p class="count text-green">{{ countApproved }}</p>
              <span class="desc">Disetujui tahun ini</span>
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
      </div>

    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import AppLayout from '../components/AppLayout.vue';
import api from '../services/api';

const balances = ref<any[]>([]);
const leaveRequests = ref<any[]>([]);
const isLoading = ref(true);
const currentYear = new Date().getFullYear();

// Ambil data Balance dan Request secara bersamaan
const fetchData = async () => {
  isLoading.value = true;
  try {
    const [balanceRes, requestRes] = await Promise.all([
      api.get('/my-balances'),
      api.get('/leave-requests') // Backend sudah kita set untuk filter by user_id jika employee
    ]);

    balances.value = balanceRes.data;
    leaveRequests.value = requestRes.data.data;
  } catch (error) {
    console.error("Gagal memuat data dashboard user", error);
  } finally {
    isLoading.value = false;
  }
};

// Kalkulasi Progress Bar
const getPercentage = (used: number, total: number) => {
  if (total === 0) return 0;
  // Hitung sisanya: (Total - Terpakai) / Total * 100
  const remaining = total - used;
  return Math.min((remaining / total) * 100, 100);
};

// Helper Icon berdasarkan nama cuti
const getLeaveIcon = (name: string) => {
  if (name.toLowerCase().includes('annual')) return '🏖️';
  if (name.toLowerCase().includes('sick')) return '🏥';
  return '📋';
};

// Computed Properties untuk Kartu Ringkasan Bawah
const countPending = computed(() => leaveRequests.value.filter(req => req.status === 'pending').length);
const countApproved = computed(() => leaveRequests.value.filter(req => req.status === 'approved').length);
const countRejected = computed(() => leaveRequests.value.filter(req => req.status === 'rejected').length);

onMounted(() => {
  fetchData();
});
</script>

<style scoped>
.dashboard-content { padding: 2rem; }
.header-actions { margin-bottom: 2rem; }
.page-title { margin: 0; font-size: 1.5rem; color: #0f172a; }
.page-subtitle { margin: 0.25rem 0 0 0; color: #64748b; font-size: 0.9rem; }
.loading-state { text-align: center; padding: 3rem; color: #64748b; }

/* Styling Kartu Balance (Progress Bar) */
.balance-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; }
.balance-card { background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.balance-title { margin: 0 0 1.25rem 0; font-size: 1rem; color: #0f172a; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; }

.progress-container { width: 100%; height: 8px; background-color: #e2e8f0; border-radius: 999px; overflow: hidden; margin-bottom: 1rem; }
.progress-bar { height: 100%; border-radius: 999px; transition: width 0.5s ease-in-out; }
.bg-blue { background-color: #4f46e5; }
.bg-green { background-color: #10b981; }

.balance-stats { display: flex; justify-content: space-between; font-size: 0.85rem; color: #64748b; }
.balance-stats strong { color: #0f172a; }

/* Styling Kartu Summary Status (Sama seperti dashboard admin) */
.summary-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
.card { background: white; padding: 1.5rem; border-radius: 12px; display: flex; align-items: flex-start; gap: 1rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.icon-wrap { font-size: 1.5rem; }
.card-info h3 { margin: 0 0 0.5rem 0; font-size: 0.9rem; color: #64748b; font-weight: normal; }
.card-info .count { margin: 0; font-size: 2rem; font-weight: bold; }
.card-info .desc { font-size: 0.75rem; color: #94a3b8; }

.text-yellow { color: #eab308; }
.text-green { color: #22c55e; }
.text-red { color: #ef4444; }
.mt-4 { margin-top: 1.5rem; }
</style>
