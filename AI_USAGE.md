# Daftar Penggunaan AI (AI Usage Tracker)

Dokumen ini berisi daftar penggunaan *Artificial Intelligence* (AI) beserta *prompt* dan *tool* yang digunakan selama proses pengembangan LeaveHub sebagai bentuk transparansi.

## 🤖 AI & Tools yang Digunakan
- **AI Assistant:** Google Gemini
- **Model / Tools:** - **Code Generation & Debugging:** Digunakan untuk menyusun logika backend (Laravel), frontend (Vue 3 Composition API), dan unit testing.
  - **Vision / Image Analysis (Multimodal):** Digunakan untuk menganalisis gambar *mockup* UI (Dashboard, Form, Tabel) yang diunggah dan menerjemahkannya ke dalam komponen Vue.js (HTML/CSS statis menjadi dinamis).

---

## 📝 Riwayat Prompt & Interaksi

Berikut adalah ringkasan tahapan *prompt* yang diberikan kepada AI selama proses pengembangan:

### 1. Setup & Konfigurasi Dasar
- **Prompt:** *"Bisakah kita melihat tampilan login terlebih dahulu sebelum masuk ke langkah berikutnya?"*
- **Tujuan:** Menyiapkan integrasi Vue (Vite) di dalam tampilan Blade Laravel dan memastikan *routing* berjalan.
- **Prompt:** *"Bagaimana cara untuk mengarahkan http://localhost:8000/ secara default akan mengarah ke http://localhost:8000/login"*
- **Tujuan:** Membuat konfigurasi *redirect* default pada Vue Router.

### 2. Pembuatan UI dari Mockup (Vision Tool)
- **Prompt:** *"[Mengunggah Gambar Dashboard Admin] ...buatkan terlebih dahulu dashboard admin dengan UI seperti pada gambar. kita test terlebih dahulu jika login apakah bisa masuk ke dashboard ini atau tidak"*
- **Tujuan:** Menghasilkan komponen `AppLayout.vue` dan `DashboardView.vue` dari gambar desain.
- **Prompt:** *"[Mengunggah Gambar Kelola User] ...buat halaman user di role admin terlebih dahulu. berikut requirementnya dan UI yang perlu dibuat: Create user, edit user, tidak ada delete, validasi max 2 user, auto-assign balance."*
- **Tujuan:** Menerjemahkan aturan bisnis kelola *user* ke dalam Backend (API) dan Frontend (Vue) sesuai gambar.

### 3. Debugging & Error Handling
- **Prompt:** *"[plugin:vite:vue] At least one <template> or <script> is required in a single file component."*
- **Tujuan:** Menyelesaikan *error* kompilasi Vite pada komponen Vue yang kosong.
- **Prompt:** *"Saat saya mencoba login, Terjadi kesalahan pada server. Silakan coba lagi. dan di network errornya adalah 500 Internal Server Error"*
- **Tujuan:** Melakukan *debugging* pada model User (menambahkan trait `HasApiTokens` untuk Laravel Sanctum).
- **Prompt:** *"Saat mencoba berganti akun, muncul 403 Forbidden"*
- **Tujuan:** Memperbaiki pengenalan *role* ('admin' vs 'employee') pada fitur *impersonation* di sisi database dan backend.

### 4. Pengembangan Fitur Inti (Leave Request)
- **Prompt:** *"Berikut penjelasan leave request: Setiap leave request dimulai dengan status pending... pending -> approved, pending -> rejected, pending -> cancelled... Admin bisa soft delete request manapun yang berstatus final..."*
- **Tujuan:** Membangun API Controller untuk *state machine* status cuti dan aturan *soft delete* berdasarkan *role*.
- **Prompt:** *"Bisakah anda hilangkan mock dummy di halaman dashboard yang juga adalah halaman leave request supaya bisa diuji dengan data real yang terdapat di database??"*
- **Tujuan:** Menghubungkan UI statis Dashboard Admin dengan API pengajuan cuti secara dinamis (*real-time data binding*).
- **Prompt:** *"[Mengunggah Gambar Form Cuti] ...ajukan cuti bisa meniru UI yang saya berikan lewat gambar. dan buatlah agar admin yang login dapat langsung mengakses halaman user (impersonate)..."*
- **Tujuan:** Membuat form pengajuan cuti beserta kalkulasi *real-time* sisa hari, dan membangun fitur autentikasi *impersonate* admin menjadi *user*.

### 5. Penyempurnaan UI User
- **Prompt:** *"[Mengunggah Gambar Sidebar & Sisa Kuota] ...saat kita masuk sebagai user. bisakah anda juga mengganti tampilan sidebarnya? ...anggap seolah-olah user yang sedang login dengan mengganti sidebar menjadi sisa kuota, ajukan cuti, dan riwayat cuti"*
- **Tujuan:** Membuat *sidebar* reaktif berdasarkan `role` dan merender halaman Sisa Kuota beserta *progress bar*.
- **Prompt:** *"[Mengunggah Gambar Riwayat Cuti] ...sekarang buat halaman riwayat cuti untuk user seperti yang tercantum pada gambar ini"*
- **Tujuan:** Membuat tabel riwayat dengan kemampuan *Cancel* (untuk pending) dan *Delete* (untuk riwayat final).

### 6. Testing & Dokumentasi
- **Prompt:** *"Unit Test: Backend, Minimal 1 test menggunakan PHPUnit. Frontend, Minimal 1 test menggunakan Vitest / Vue Test Utils. Sekarang lanjut untuk membuat unit test"*
- **Tujuan:** Menyusun skenario *test* otomatis untuk memastikan *auto-assign quota* di backend berjalan, dan *render form* di frontend berhasil.
- **Prompt:** *"Sertakan file README.md berisi: cara instalasi, cara menjalankan aplikasi, dan cara menjalankan testing."*
- **Tujuan:** Menghasilkan dokumentasi teknis yang rapi untuk instalasi proyek.
- **Prompt:** *"Saya juga perlu menyertakan file AI_USAGE.md. Isinya daftar prompt yang digunakan beserta nama mcp dan toolnya saat menggunakan AI..."*
- **Tujuan:** Menghasilkan dokumen transparansi penggunaan AI (dokumen ini).
