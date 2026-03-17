<template>
  <AppLayout>
    <div class="dashboard-content">
      <div class="header-actions">
        <div>
          <h1 class="page-title">Semua Leave Request</h1>
          <p class="page-subtitle">Kelola dan respons permohonan cuti dari semua user.</p>
        </div>
        <div class="role-toggle">
          <span class="label">Role:</span>
          <button class="btn-toggle active">Admin</button>
          <button class="btn-toggle">User</button>
        </div>
      </div>

      <div class="summary-cards">
        <div class="card">
          <div class="icon-wrap text-yellow">🏆</div>
          <div class="card-info">
            <h3>Pending</h3>
            <p class="count text-yellow">2</p>
            <span class="desc">Menunggu keputusan</span>
          </div>
        </div>
        <div class="card">
          <div class="icon-wrap text-green">✓</div>
          <div class="card-info">
            <h3>Approved</h3>
            <p class="count text-green">3</p>
            <span class="desc">Disetujui</span>
          </div>
        </div>
        <div class="card">
          <div class="icon-wrap text-red">✗</div>
          <div class="card-info">
            <h3>Rejected</h3>
            <p class="count text-red">1</p>
            <span class="desc">Ditolak</span>
          </div>
        </div>
      </div>

      <div class="table-container">
        <h3 class="section-title">Perlu Tindakan</h3>
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
            <tr>
              <td>Budi Santoso</td>
              <td>Annual Leave</td>
              <td>20 - 22 Mar 2026</td>
              <td>3</td>
              <td>Liburan keluarga</td>
              <td><span class="badge badge-warning">pending</span></td>
              <td>
                <button class="btn-action btn-approve">Approve</button>
                <button class="btn-action btn-reject">Reject</button>
              </td>
            </tr>
            <tr>
              <td>Sari Dewi</td>
              <td>Sick Leave</td>
              <td>18 Mar 2026</td>
              <td>1</td>
              <td>Sakit demam</td>
              <td><span class="badge badge-warning">pending</span></td>
              <td>
                <button class="btn-action btn-approve">Approve</button>
                <button class="btn-action btn-reject">Reject</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="action-forms">
        <div class="action-card border-green">
          <h4>Approve Request — Budi Santoso</h4>
          <p><strong>Annual Leave</strong> · 20 - 22 Mar 2026 (3 hari)</p>
          <p class="reason">Alasan: Liburan keluarga</p>
          <p class="balance-info">Sisa balance setelah approve: <span class="text-blue">7/12 hari</span></p>

          <div class="form-group">
            <label>Catatan Admin (opsional)</label>
            <input type="text" placeholder="Approved, selamat berlibur." class="input-field" />
          </div>
          <div class="form-actions">
            <button class="btn-action btn-approve">Konfirmasi Approve</button>
            <button class="btn-action btn-cancel">Batal</button>
          </div>
        </div>

        <div class="action-card border-red">
          <h4>Reject Request — Sari Dewi</h4>
          <p><strong>Sick Leave</strong> · 18 Mar 2026 (1 hari)</p>
          <p class="reason">Alasan: Sakit demam</p>

          <div class="form-group">
            <label>Catatan Admin (opsional)</label>
            <input type="text" placeholder="Alasan penolakan..." class="input-field" />
          </div>
          <div class="form-actions">
            <button class="btn-action btn-reject">Konfirmasi Reject</button>
            <button class="btn-action btn-cancel">Batal</button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '../components/AppLayout.vue';
</script>

<style scoped>
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
.text-blue { color: #3b82f6; }

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
.balance-info { margin-bottom: 1rem !important; font-size: 0.85rem !important; }
.form-group { margin: 1rem 0; display: flex; flex-direction: column; gap: 0.5rem; }
.form-group label { font-size: 0.8rem; color: #64748b; }
.input-field { padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; outline: none; }
.form-actions { display: flex; gap: 0.5rem; }
</style>
