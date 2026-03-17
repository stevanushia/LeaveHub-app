# LeaveHub - Leave Request Management System

LeaveHub adalah sistem manajemen permohonan cuti karyawan yang dibangun dengan arsitektur Monorepo. Aplikasi ini memadukan backend API yang tangguh dengan antarmuka frontend yang reaktif.

## 🛠️ Stack Teknologi
- **Backend:** Laravel 12 (PHP)
- **Frontend:** Vue.js 3 + TypeScript (Composition API)
- **State Management:** Pinia
- **HTTP Client:** Axios
- **Database:** PostgreSQL
- **Authentication:** Laravel Sanctum (Personal Access Token)

## 📋 Prasyarat
Sebelum menginstal aplikasi, pastikan sistem Anda sudah memiliki:
- PHP >= 8.2
- Composer
- Node.js & NPM
- PostgreSQL (Database Server)

---

## 🚀 Cara Instalasi

1. **Clone atau Ekstrak Repository**
   Buka terminal dan arahkan ke direktori proyek.

2. **Instal Dependensi Backend (PHP)**
   ```bash
   composer install
   ```

3. **Instal Dependensi Frontend (Node)**
   ```bash
   npm install
   ```

4. **Konfigurasi Environment**
   Salin file `.env.example` menjadi `.env`:
   ```bash
   cp .env.example .env
   ```
   Buka file `.env` dan sesuaikan kredensial koneksi PostgreSQL Anda:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=db_leavehub
   DB_USERNAME=username_postgres
   DB_PASSWORD=password_postgres
   ```

5. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

6. **Migrasi Database & Seeding Data**
   Jalankan perintah ini untuk membuat struktur tabel dan mengisi data *dummy* awal (Master Cuti & Akun Admin/User):
   ```bash
   php artisan migrate:fresh --seed
   ```

---

## 💻 Cara Menjalankan Aplikasi

Karena ini adalah monorepo (Frontend dan Backend berjalan berdampingan), Anda perlu membuka **dua terminal** secara terpisah.

**Terminal 1: Menjalankan Backend Laravel**
   ```bash
   php artisan serve
   ```
   *Server backend akan berjalan di http://localhost:8000*

**Terminal 2: Menjalankan Frontend Vite**
   ```bash
   npm run dev
   ```

**Akses Aplikasi:**
Buka browser Anda dan kunjungi `http://localhost:8000`. Aplikasi akan otomatis mengarahkan Anda ke halaman Login.

**Kredensial Login Default (Dari Seeder):**
- **Admin:** `admin@energeek.id` | Password: `password123`

---

## 🧪 Cara Menjalankan Testing

Proyek ini dilengkapi dengan Unit Test untuk memastikan kualitas dan integritas fitur.

### 1. Backend Testing (PHPUnit)
Menguji logika validasi, autentikasi, dan manipulasi database (seperti pengisian *leave balance* otomatis).
   ```bash
   php artisan test
   ```

### 2. Frontend Testing (Vitest + Vue Test Utils)
Menguji proses rendering komponen UI dan integrasi statis frontend.
   ```bash
   npx vitest run
   ```

---
