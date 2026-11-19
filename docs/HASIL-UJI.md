# Hasil pengujian nyata

Tanggal: 13 November 2026, Windows / Laragon. Proyek dibangun pada folder kerja mandiri,
bukan project lokal lama pengguna.

| Pemeriksaan | Hasil |
|---|---|
| PHP CLI | 8.3.33, ekstensi pdo_mysql/pdo_sqlite/fileinfo/gd aktif |
| Composer | 2.10.3; `composer validate --no-check-publish` berhasil |
| Node / npm | 24.22.1 / 11.19.1 |
| Laravel | 12.72.0 |
| Fortify / Inertia Laravel | 1.42.0 / 2.0.28 |
| nwidart/laravel-modules | 12.0.5; dua modul Enabled |
| React / Inertia React / TypeScript / Vite | 19.2.0 / 2.1.4 / 5.9.2 / 7.1.5 |
| `php artisan route:list` | Berhasil; auth Fortify, settings, dashboard, enam route UserManagement dan enam route Pendaftaran terdaftar |
| `php artisan module:list` | Pendaftaran dan UserManagement Enabled |
| MySQL migration + seed | Berhasil pada MySQL 8.4.3, database validasi terpisah; termasuk dua migration modul dan kolom two_factor |
| Pest SQLite `:memory:` | 66 passed, 285 assertions |
| Pest MySQL | 66 passed, 285 assertions, database terpisah `codex_modul9_20261113_a7f2` |
| `npm run types` / `tsc --noEmit` | Berhasil; termasuk TSX modul dan resolver client/SSR, tidak ada error overload |
| `npm run build` | Berhasil (bagian pertama build:ssr), 2709 modul ditransformasikan |
| `npm run build:ssr` | Berhasil; 88 modul pada SSR; bundle server tidak berarti proses SSR runtime diuji |
| PHP lint | Semua file PHP aplikasi/modul lolos; log terlampir |
| PowerShell setup | Parser lolos; `setup.ps1 -SkipSeed -SkipNpm` berhasil pada DB validasi. Seed, instalasi npm dan build diuji tersendiri; log setup-check.txt |

## Cakupan

Tes mencakup register/login/logout/reset password/settings/2FA bawaan kit, default role
user walau input dimanipulasi, redirect tamu, larangan user membuka/memutasi route admin,
dashboard sesuai role, CRUD user, pencarian/filter/pagination, email unik, password lama
saat edit kosong, penggantian password, larangan self-demotion/delete, data JSON pendaftar,
validasi 422, ID tidak ditemukan 404, semua format upload yang diizinkan, zip dan ukuran
lebih dari 2 MB yang ditolak, file dipertahankan ketika edit kosong, file lama dihapus ketika
diganti, dan file ikut dihapus bersama record. Seeder diuji menghasilkan akun demo yang
bisa login tanpa challenge 2FA palsu.

Log teks: test-sqlite.txt, test-mysql.txt, migrate-mysql.txt, routes.txt, modules.txt,
php-lint.txt, typescript.txt dan setup-check.txt. Tes SQLite default tidak memakai database
MySQL pengguna. Storage::fake mencegah tes upload menyentuh disk berkas aplikasi asli.

## Keterbatasan yang harus dibedakan dari tes lulus

- Browser interaktif ke `http://127.0.0.1:8009/login` ditolak oleh sistem persetujuan alat.
  Tidak ada screenshot UI aplikasi, pengamatan DevTools Network, pemeriksaan tata letak
  desktop/mobile, atau pembuktian tautan file melalui browser yang diklaim sudah dilakukan.
- Test HTTP Laravel/Pest menguji backend dan respons Inertia/JSON; tes tersebut tidak
  menjalankan event React di browser. Build/TypeScript memeriksa kompilasi frontend.
- storage:link berhasil dibuat di Windows pembuat. Symlink harus dibuat ulang di folder
  ekstraksi pengguna; symlink absolut dari mesin pembuat tidak dimasukkan ke ZIP.
- Build SSR lulus, tetapi rendering lewat proses server SSR yang berjalan tidak diuji.
- Proyek lama `pertemuan9_hmvc_react` milik pengguna tidak tersedia. Paket ini merupakan
  proyek baru, sehingga tidak membuktikan kompatibilitas dengan perubahan pribadi di
  project lama tersebut.

Pada percobaan awal, tes auth dijalankan sebelum manifest Vite selesai dan lima tes gagal.
Sesudah build selesai, seluruh suite diulang dan lulus. Pembatasan akses tulis sandbox pada
npm/PHPUnit/SSR diselesaikan melalui eksekusi yang disetujui. Hasil tabel merujuk pada
pemeriksaan akhir yang berhasil, bukan mengabaikan kegagalan awal tersebut.
