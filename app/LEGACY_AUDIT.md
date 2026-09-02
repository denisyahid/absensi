# Audit kode Laravel lama

Audit ini membedakan data yang masih dipakai API baru dan kode yang baru boleh
dihapus setelah cut-over produksi.

## Tabel yang dipakai backend React

| Tabel | Pemakaian |
|---|---|
| `users` | akun, profil, direktori pegawai |
| `api_tokens` | token API; dibuat otomatis oleh `backend.php` |
| `api_email_verifications` | token verifikasi; dibuat otomatis |
| `password_reset_tokens` | reset password |
| `absens` | kehadiran masuk/pulang dan laporan |
| `schedules` | jadwal bulanan |
| `user_types` | master jenis shift |
| `logbooks` | kegiatan pegawai |
| `rujukan_models` | perjalanan dinas/rujukan |
| `rujukan_user` | peserta perjalanan dan data PNS |
| `pns` | direktori PNS |
| `roles`, `permissions`, dan pivot Spatie | role/permission API |

## Tabel lama yang tidak dipakai API baru

- `masuks` dan `pulangs`: alur lama sebelum data disatukan di `absens`.
- `datapegawais`: data profil sudah berada pada `users`.
- `user_types` tetap dipertahankan karena dipakai sebagai master pilihan shift.
- Tabel queue, cache, session, serta permission Laravel boleh tetap ada selama
  aplikasi Laravel masih dijalankan paralel.

## Kode yang dapat diarsipkan setelah cut-over

- `app/Livewire/**`
- `resources/views/**`
- controller web Laravel dan route Volt/Folio
- dependency Livewire, Volt, Folio, Breeze, dan Laravel Vite lama

## Keputusan keselamatan

Kode dan tabel lama **tidak dihapus pada tahap ini** karena penghapusan akan
memutus rollback. Setelah React stabil di produksi dan backup tervalidasi:

1. Jalankan `app/deploy/backup.sh`.
2. Pastikan arsip database dan `storage/app/public` dapat dipulihkan.
3. Ekspor data `masuks`, `pulangs`, dan `datapegawais` bila masih dibutuhkan.
4. Arsipkan commit Laravel lama dengan tag Git.
5. Baru hapus kode/tabel yang tercantum sebagai tidak dipakai.

Menunda penghapusan bukan kekurangan migrasi; ini adalah mekanisme rollback
untuk menjaga data operasional rumah sakit.
