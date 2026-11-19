# Pemetaan implementasi ke Modul 9

Seluruh 112 halaman dibaca, termasuk kode, struktur folder (gambar halaman 10–11),
pengujian dan tugas akhir modul. Timestamp depan migration merupakan waktu pembuatan;
suffix nama migration, urutan dan skemanya dipertahankan. Folder bawaan modul yang
tidak dipakai (Blade/assets dan Vite/package.json modul) tidak disertakan.

| PDF | Implementasi |
|---|---|
| 1–6: tujuan, konsep, rancangan | Laravel React Starter Kit resmi; autentikasi bersama di luar HMVC; React di masing-masing modul; matriks role sesuai rancangan. |
| 7–14: Tahap A | `composer.json` memakai nwidart 12 dan merge-plugin. `config/modules.php`, `stubs/nwidart-stubs`, `vite-module-loader.js`, `modules_statuses.json`. Dua modul aktif; `routes/api.php` kosong tanpa Sanctum. Resolver client/SSR, Tailwind @source, tsconfig include modul dan alias @/. |
| 14–24: Tahap B role/auth | Migration role, `UserRole`, `User` cast/default/helpers, state factory admin, DatabaseSeeder dua akun, `CreateNewUser` memaksa role user, Fortify login/logout/throttle/2FA tetap dipertahankan. |
| 24–36: Tahap B bersama | `lang/id/auth.php`, shared auth/flash, `use-shared-props.ts`, `alert-message.tsx`, `confirm-dialog.tsx`, sidebar admin/user, DashboardController dan halaman dashboard. |
| 36–39: Tahap C | EnsureUserHasRole dan alias role di bootstrap/app.php. Middleware auth selalu mendahului role. |
| 39–59: Tahap D | StoreUserRequest, UpdateUserRequest, UserManagementController, resource routes kecuali show, halaman index/create/edit dan komponen user-form. Pencarian nama/email, filter role, pagination 10, flash, password opsional saat edit, self-demotion dan self-deletion dicegah di server. |
| 60–67: Tahap E data | `create_daftars_table`, `add_berkas_to_daftars_table`, model Daftar dengan berkas_url, factory, DaftarRequest, email unik/regex HP/upload validasi. |
| 68–80: Tahap E CRUD AJAX | PendaftaranController, enam route, helper http.ts dan halaman index.tsx dengan state, useEffect, useMemo, fetch, FormData, _method PUT, pesan 422 per input, dialog, FileLink, loading/processing, pencarian lima kolom, reload data setelah mutasi. |
| 81–86: penjelasan AJAX | Perilaku tetap mengikuti kode praktikum: React escape teks, file public, penghapusan file lama dan file saat hapus record, dialog reset input file. |
| 86–104: Tahap F | Migration/seed dan route/module listing diuji; checklist browser terperinci disertakan. Pengujian browser interaktif dibatasi persetujuan alat; tidak ada klaim screenshot aplikasi. |
| 104–108: 9.6 Pest | UserManagementTest.php dan PendaftaranTest.php mengikuti tes modul; tes auth/settings starter kit dipertahankan. ModuleRegressionTest menambah cakupan hak akses mutasi, password, filter/pagination, upload, dashboard dan akun demo. |
| 109: 9.7 perbandingan | UserManagement mengembalikan Inertia/redirect/session flash; Pendaftaran memakai fetch/JSON/state tanpa perpindahan halaman. |
| 109–110: 9.8 tugas | SiPustaka adalah project baru `sipustaka_hmvc_react` / `db_sipustaka`: Buku Inertia (5 kolom, admin CRUD/user baca), Peminjaman AJAX (5 kolom, upload), UserManagement, role, validasi dan pembuktian Network/storage. Tugas kedua ialah adaptasi tema tugas akhir pribadi. Keduanya berbeda dari proyek praktikum Tahap A–F yang diminta, dan tidak diklaim selesai oleh paket ini. |
| 111–112: 9.9 pengumpulan | Checklist kebutuhan laporan dan demo ada pada CHECKLIST-TAHAP-F.md. Identitas mahasiswa, logo, repository GitHub, serta screenshot hasil mesin mahasiswa tidak dibuat-buat. |

## Penyesuaian kompatibilitas yang disengaja

1. Starter kit dipilih pada commit Laravel 12/Inertia 2, bukan main terbaru Laravel 13/Inertia 3.
   File dan fitur Fortify/React/shadcn dari starter kit resmi tetap digunakan.
2. Resolver diberi tipe dan dipakai bersama oleh app.tsx/ssr.tsx melalui lib/resolve-page.ts.
   Ia mengembalikan komponen default; ini memperbaiki error overload TypeScript tanpa
   menonaktifkan pemeriksaan tipe. Jalur relatif glob disesuaikan dengan lokasi helper.
3. Blade root memuat entry app.tsx, karena string `NamaModul::index` bukan jalur halaman
   utama di resources/js/pages. Pemisahan chunk halaman dikelola oleh Vite.
4. Factory 2FA bawaan tetap dipakai tes starter kit; seeder demo memakai withoutTwoFactor()
   agar akun admin/user tidak meminta kode dari secret palsu factory.
5. `composer run dev` menjalankan PHP serve dan Vite tanpa Pail yang membutuhkan pcntl
   pada Windows. `optimize-autoloader` dimatikan untuk menghindari pemindaian mahal di
   lingkungan Windows pembuat; autoload PSR-4 dan merge-plugin tetap aktif.
6. Module service provider hanya mendaftarkan route/config/migration/event; tidak memuat
   view Blade yang memang dihapus pada Tahap A. Event provider dan seeder kosong serta
   folder tests bawaan modul tetap disertakan sesuai gambar struktur PDF.
7. setup.ps1, scripts/create-database.php, dokumentasi dan tes regresi ditambahkan untuk
   kemudahan instalasi dan verifikasi; keduanya tidak mengganti pola CRUD praktikum.
