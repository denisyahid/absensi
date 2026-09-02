# SiHadir — React + API PHP mandiri

Folder `react/` di root repository berisi target migrasi bertahap aplikasi
absensi RSUD Malangbong. Folder ini berdiri sendiri dan tidak berada di dalam
`app/` milik Laravel:

- frontend React JS/Vite di `react/src/`;
- seluruh backend baru dalam **satu file**, `react/backend.php`;
- konfigurasi deployment, backup, dan health check di `react/deploy/`;
- pengujian frontend/API client di `react/src/test/` dan smoke test HTTP di
  `react/tests/api-smoke.mjs`.

Kode Laravel/Livewire lama tetap dipertahankan di repository sebagai jalur
rollback sampai cut-over produksi disetujui. Rincian dependensi data dan urutan
cleanup aman tersedia di [`LEGACY_AUDIT.md`](LEGACY_AUDIT.md).

## Cara kerja dan kompatibilitas data

`backend.php` tidak melakukan bootstrap Laravel dan tidak membutuhkan Composer
saat runtime. Backend memakai PDO dan tabel database Laravel yang telah ada.
Hash password bcrypt tetap kompatibel dengan `password_verify()`.

Backend memakai tabel `users`, `absens`, `schedules`, `user_types`, `logbooks`,
`rujukan_models`, `rujukan_user`, `pns`, `password_reset_tokens`, serta tabel
role/permission Spatie. Tabel `api_tokens` dan `api_email_verifications` dibuat
otomatis saat dibutuhkan.

## Fitur yang tersedia

- Login, sesi Bearer token, logout, lupa/reset password, dan verifikasi email.
- Dashboard, absensi masuk/pulang dengan foto, shift lintas hari, dan statistik.
- Profil dan pengelolaan akun pengguna.
- Kalender jadwal, logbook kegiatan, dan file bukti privat.
- Rekap kehadiran, laporan logbook, ekspor CSV, dan laporan jadwal.
- Perjalanan dinas/rujukan, peserta internal/PNS, dokumen bukti, dan konfirmasi.
- Cetak SPPD, SPPD Srikandi, rincian biaya/kwitansi, logbook, kehadiran,
  jadwal, dan bukti pelayanan ambulance melalui dialog cetak browser/PDF.
- CRUD role/permission Spatie, permission role, role pengguna, dan permission
  langsung pengguna.

Permission sistem awal adalah `manage-users`, `manage-schedules`,
`manage-logbooks`, `view-all-reports`, `manage-referrals`, dan `manage-access`.
Role awal adalah `super-admin`, `manajemen`, dan `pegawai`. Akun berjabatan
`manajemen` tetap dikenali untuk kompatibilitas aplikasi lama.

## Prasyarat

- Node.js 20+ dan npm;
- PHP 8.2+ dengan `PDO`, `mbstring`, dan `fileinfo`;
- `pdo_sqlite` untuk SQLite atau `pdo_mysql` untuk MySQL/MariaDB;
- database yang telah menjalankan migration Laravel lama, termasuk migration
  Spatie Permission.

Composer/Laravel hanya diperlukan untuk menyiapkan atau memigrasikan database
lama, bukan untuk menjalankan API baru.

## Menjalankan untuk development

Dari root repository, siapkan database bila belum pernah dilakukan:

```bash
cp .env.example .env
composer install
php artisan key:generate
touch database/database.sqlite       # hanya SQLite
php artisan migrate --seed
```

Pastikan `.env` root menggunakan timezone dan URL yang benar. Contoh lokal:

```env
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://localhost:5173
FRONTEND_URL=http://localhost:5173
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
MAIL_MAILER=log
API_ALLOWED_ORIGINS=http://localhost:5173
```

Jalankan API dari terminal pertama:

```bash
cd react
php -S 0.0.0.0:8080
```

Jalankan React dari terminal kedua:

```bash
cd react
cp .env.example .env.local
npm ci
npm run dev -- --host 0.0.0.0
```

Buka `http://localhost:5173`. Vite meneruskan `/backend.php` ke
`VITE_PHP_SERVER`. Akun seeder menggunakan password `password`; contoh akun
adalah `deni@example.com`.

Jika tabel `users` benar-benar kosong, akun pertama dapat dibuat satu kali
melalui `POST backend.php?route=auth/bootstrap`. Pada database lama yang sudah
berisi akun, kelola akun dari menu **Data Pengguna**.

## Environment

Frontend membaca `react/.env.local`:

```env
VITE_API_URL=/backend.php
VITE_PHP_SERVER=http://127.0.0.1:8080
```

Backend membaca `.env` di root repository. Variabel penting:

- `APP_TIMEZONE`, `APP_DEBUG`, `APP_URL`, dan `FRONTEND_URL`;
- `DB_CONNECTION=sqlite|mysql|mariadb` beserta konfigurasi database;
- `API_ALLOWED_ORIGINS`, berupa satu atau beberapa origin dipisahkan koma;
- `MAIL_MAILER`, `MAIL_FROM_ADDRESS`, dan `MAIL_FROM_NAME`.

`MAIL_MAILER=log` menyimpan email reset/verifikasi ke
`storage/logs/api-mail.log`. Nilai lain memakai fungsi `mail()` PHP, sehingga
server produksi harus memiliki MTA/sendmail yang sudah dikonfigurasi. Jangan
menggunakan `API_ALLOWED_ORIGINS=*` di produksi.

## Pengujian

```bash
cd react
npm ci
npm test                    # Vitest + Testing Library
npm run build               # build produksi
npm audit --audit-level=high
php -l backend.php          # syntax check PHP native
```

Smoke test API memerlukan database test yang sudah dimigrasikan/di-seed dan API
lokal yang aktif:

```bash
cd react
php -S 127.0.0.1:8080 >/tmp/absensi-api.log 2>&1 &
API_URL=http://127.0.0.1:8080/backend.php node tests/api-smoke.mjs
```

Smoke test mencakup autentikasi, enforcement role/permission, pengguna, jadwal,
absensi, upload/file, logbook, perjalanan, laporan, dan reset password. Workflow
`.github/workflows/ci.yml` menjalankan rangkaian tersebut pada PHP 8.3/SQLite dan
Node.js 22.

## Build dan deployment produksi

```bash
cd react
npm ci
npm run build
```

Hasil build berada di `react/dist/` dan tidak dimasukkan ke Git. Gunakan salah satu
contoh vhost berikut, lalu sesuaikan domain, root repository, sertifikat HTTPS,
dan socket PHP-FPM:

- `deploy/nginx.conf.example`
- `deploy/apache-vhost.conf.example`

Kedua contoh menyajikan SPA dari `dist`, meneruskan hanya `/backend.php` ke
PHP-FPM, dan memberi fallback React Router ke `index.html`. Untuk deployment
frontend/API beda domain, build dengan `VITE_API_URL` absolut dan masukkan origin
frontend persis ke `API_ALLOWED_ORIGINS`.

Pastikan user PHP-FPM dapat menulis ke `storage/react/public` dan `storage/logs`,
tetapi `.env`, database SQLite, source PHP, dan folder backup tidak disajikan
sebagai file statis.

## Operasional, backup, dan health check

Sebelum cut-over atau perubahan skema:

```bash
BACKUP_DIR=/srv/backup/absensi react/deploy/backup.sh
```

Skrip mencadangkan database SQLite atau dump MySQL, `storage/react/public`, dan
snapshot `.env` ke arsip bertimestamp. Simpan backup di lokasi terenkripsi di
luar web root dan lakukan uji restore.

Health check:

```bash
react/deploy/healthcheck.sh 'https://absensi.example.go.id/backend.php?route=health'
```

Endpoint sehat mengembalikan `status: ok` dan status koneksi database. Jalankan
health check dari monitoring terjadwal setelah deployment.

## Ringkasan endpoint

Semua route dikirim sebagai `backend.php?route=...`.

- Publik: `health`, `auth/login`, `auth/bootstrap`, `auth/forgot-password`,
  `auth/reset-password`, dan `auth/verification/verify`.
- Autentikasi: `auth/me`, `auth/logout`, `auth/verification/send`.
- Operasional: `dashboard`, `attendance`, `profile`, `users`, `directory`,
  `schedules`, `logbooks`, `pns`, dan `referrals`.
- Laporan/file: `reports/attendance`, `reports/logbooks`, dan `files`.
- Hak akses: `access`, `access/assign`, `roles`, `permissions`, dan sinkronisasi
  permission role.

Selain endpoint publik, request harus mengirim `Authorization: Bearer <token>`.
