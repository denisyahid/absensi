# Kemajuan Migrasi Laravel → React + PHP API

Terakhir diperbarui: **2 September 2026**

## Status akhir implementasi

Implementasi aplikasi baru di folder `app/` **telah selesai untuk seluruh ruang
lingkup kode yang ditemukan pada aplikasi Laravel lama**. Frontend menggunakan
React JS, sedangkan seluruh backend baru berada dalam satu file
`app/backend.php` dan tidak melakukan bootstrap Laravel.

Migrasi tetap dilakukan bertahap: source Laravel/Livewire lama dan tabel lama
belum dihapus supaya rollback masih aman saat staging/cut-over. Menyimpan jalur
rollback ini adalah keputusan transisi, bukan fitur migrasi yang tertunda.

## Sudah selesai

### Fondasi dan arsitektur

- [x] Membuat frontend React JS berbasis Vite langsung di `app/src`.
- [x] Membuat keseluruhan backend baru dalam **satu file PHP**,
  `app/backend.php`.
- [x] Memisahkan frontend/backend melalui API JSON dengan Bearer token.
- [x] Memakai skema database Laravel lama melalui PDO tanpa bootstrap Laravel.
- [x] Mendukung SQLite, MySQL, dan MariaDB dari `.env` root.
- [x] Menjaga kompatibilitas password bcrypt dan data lama.
- [x] Menambahkan layout responsif desktop/mobile, state loading/error/empty,
  modal, pagination, pencarian, dan navigasi role-aware.
- [x] Menambahkan CORS terkontrol, security header, token hash dan expiry,
  validasi MIME/ukuran upload, serta proteksi path traversal.

### Autentikasi, email, dan profil

- [x] Login, pemeriksaan sesi, logout, ingat perangkat, dan penanganan token
  kedaluwarsa.
- [x] Inisialisasi akun manajemen pertama ketika tabel users kosong.
- [x] Lupa password, token reset satu kali dengan expiry, dan reset password.
- [x] Pengiriman email melalui log atau fungsi `mail()` PHP.
- [x] Verifikasi email, kirim ulang verifikasi, dan verifikasi ulang saat email
  profil berubah.
- [x] Edit nama, email, nomor HP, foto profil, dan password dengan verifikasi
  password lama.

### Role dan permission Spatie

- [x] Membaca/menggunakan tabel `roles`, `permissions`, `model_has_roles`,
  `model_has_permissions`, dan `role_has_permissions`.
- [x] CRUD role serta permission.
- [x] Sinkronisasi permission per role.
- [x] Assignment role dan permission langsung per pengguna.
- [x] Bootstrap role `super-admin`, `manajemen`, `pegawai` dan permission sistem.
- [x] Enforcement permission pada API pengguna, jadwal, logbook, laporan,
  perjalanan dinas, dan pengelolaan hak akses.
- [x] Enforcement permission pada route/menu React.
- [x] Kompatibilitas akun lama berjabatan `manajemen`.

### Dashboard dan absensi

- [x] Dashboard profil, statistik bulanan, waktu server, dan status kehadiran.
- [x] Seluruh pilihan shift/jam kerja lama, termasuk shift malam lintas hari.
- [x] Absen masuk/pulang dengan kamera atau file foto.
- [x] Validasi urutan absensi dan penyimpanan foto privat.
- [x] Perhitungan keterlambatan dan pulang lebih awal.

### Data pengguna dan jadwal

- [x] Daftar pengguna, filter jabatan, pencarian, dan pagination.
- [x] Tambah, detail, edit, dan hapus akun/foto/password.
- [x] Proteksi penghapusan akun aktif atau akun yang masih memiliki relasi.
- [x] Direktori pegawai untuk peserta perjalanan dinas.
- [x] Kalender jadwal bulanan seluruh pegawai atau jadwal pribadi.
- [x] Tambah, ubah, kosongkan shift, dan dukungan jadwal lintas hari.
- [x] Laporan jadwal khusus cetak/PDF browser.

### Logbook dan laporan

- [x] Daftar logbook dengan periode, pencarian, pagination, dan pembatasan akses.
- [x] Tambah, edit, hapus logbook serta upload bukti kegiatan.
- [x] Laporan kehadiran bulanan lengkap dengan jadwal, status, keterlambatan,
  pulang awal, filter, dan ekspor CSV.
- [x] Laporan logbook bulanan per pegawai.
- [x] Dokumen cetak/PDF browser untuk rekap kehadiran dan logbook.

### Perjalanan dinas / rujukan dan dokumen

- [x] Daftar/filter, tambah, detail, edit, hapus, dan konfirmasi perjalanan.
- [x] Pembatasan akun pegawai ke perjalanan yang diikutinya.
- [x] Pemilihan banyak peserta pegawai dan data PNS pendamping.
- [x] Upload bukti rujukan/kuitansi bensin dan private file serving.
- [x] Nomor surat otomatis dengan format lama.
- [x] Cetak SPPD format lama.
- [x] Cetak SPPD Srikandi.
- [x] Cetak lembar SPD, rincian biaya perjalanan, dan kwitansi.
- [x] Cetak Bukti Pelayanan Ambulance.

### Kualitas, operasional, dan transisi

- [x] Menambahkan 9 automated test frontend/API client memakai Vitest,
  jsdom, dan Testing Library.
- [x] Menambahkan smoke test API end-to-end untuk autentikasi, permission,
  pengguna, jadwal, absensi, upload/file, logbook, perjalanan, laporan, dan
  reset password.
- [x] Menambahkan GitHub Actions untuk PHP 8.3/SQLite dan Node.js 22.
- [x] Menambahkan contoh Nginx dan Apache untuk PHP-FPM + fallback React Router.
- [x] Menambahkan skrip backup SQLite/MySQL + storage + environment.
- [x] Menambahkan skrip health check API.
- [x] Menyelesaikan audit kode/tabel lama dan prosedur cleanup aman di
  `app/LEGACY_AUDIT.md`.
- [x] Menyelaraskan dokumentasi setup, environment, test, deployment, backup,
  dan endpoint di `app/README.md`.

## Hasil verifikasi

- [x] `npm test`: **3 file, 9 test lulus**.
- [x] `npm run build`: **berhasil**, 1.606 modul ditransformasi.
- [x] `npm audit --audit-level=high`: **0 vulnerability**.
- [x] Parser PHP alternatif: syntax `app/backend.php` valid.
- [x] Integrasi nyata menggunakan runtime PHP 8.3 WebAssembly + PDO SQLite:
  health, login, access bootstrap, pengguna, jadwal, absensi masuk/pulang,
  upload/hapus logbook, perjalanan/peserta/konfirmasi/hapus, laporan, serta
  reset password seluruhnya lulus.
- [x] `bash -n` untuk skrip deployment dan `git diff --check`: lulus.

## Belum selesai — kegiatan environment produksi (bukan kekurangan kode)

Item berikut membutuhkan domain, server, credential, serta data operasional milik
pengelola dan tidak dapat diselesaikan hanya dari repository:

- [ ] Menetapkan domain produksi, sertifikat HTTPS, socket PHP-FPM, credential
  database, MTA email, dan `API_ALLOWED_ORIGINS` aktual.
- [ ] Menjalankan UAT staging dengan pengguna RSUD untuk kamera ponsel, timezone,
  shift malam, dan hasil cetak fisik.
- [ ] Membandingkan rekap satu bulan terhadap database/laporan produksi sebelum
  mengalihkan trafik.
- [ ] Menjalankan backup dan **uji restore**, lalu cut-over bertahap dengan
  monitoring health/error.
- [ ] Setelah masa paralel disetujui dan rollback tidak lagi diperlukan:
  arsipkan lalu hapus source/tabel lama sesuai `app/LEGACY_AUDIT.md`.

## Rencana cut-over

1. **Implementasi — selesai:** React, single-file PHP API, seluruh fitur, dokumen,
   permission, test, dan konfigurasi operasional.
2. **Staging:** pasang build dengan contoh vhost, isi environment produksi,
   migrasikan salinan database, dan jalankan smoke test/UAT.
3. **Paralel:** jalankan Laravel lama dan React baru selama periode validasi.
4. **Cut-over:** backup tervalidasi, arahkan trafik ke React, dan pantau API.
5. **Cleanup:** hanya setelah persetujuan, dekomisioning Laravel lama secara
   bertahap mengikuti audit; jangan hapus data sebelum retensi disepakati.
