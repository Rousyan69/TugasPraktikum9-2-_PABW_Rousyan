# Praktikum Modul 9 — HMVC Laravel React

Proyek mandiri `pertemuan9_hmvc_react`, mengikuti subbab 9.5 Tahap A–F dan
pengujian Pest 9.6 dari **Modul 9 - HMVC Starterkit React(1).pdf (112 halaman)**.
Ini proyek baru; tidak mengubah project lokal lama yang belum diunggah.

## Menjalankan di Windows / Laragon

1. Ekstrak ZIP ke lokasi pendek, misalnya `C:\laragon\www\pertemuan9_hmvc_react`.
   Jangan menumpuknya di folder project lama. Aktifkan MySQL lewat Laragon.
2. Buka PowerShell/terminal Laragon di folder yang berisi `artisan` dan `setup.ps1`.
   Gunakan PHP 8.2+ (diuji PHP 8.3), Composer 2, Node **20.19+ atau 22.12+**, npm.
   PHP harus mengaktifkan `pdo_mysql`, `pdo_sqlite` (tes), `fileinfo`, `gd` (tes gambar),
   `mbstring`, `openssl`, `curl`, `zip`, `dom`, `xml`, `intl`.
3. Jalankan:

```powershell
php -v
composer --version
node -v
npm.cmd -v
Copy-Item .env.example .env
notepad .env
```

Konfigurasi awal sudah sesuai modul:

```dotenv
APP_LOCALE=id
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hmvc_react
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan port/password jika MySQL Anda berbeda. Simpan lalu jalankan:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\setup.ps1
php artisan serve
```

Setup memasang dependency terkunci, membuat database bila belum ada, menghasilkan
APP_KEY, menjalankan migration dan seed, membuat storage link, menghasilkan route
Wayfinder, memeriksa TypeScript, serta membangun frontend. Setup **tidak menjalankan
migrate:fresh**. Gunakan database khusus praktikum. Pada instalasi yang sudah berisi
akun seed, jalankan ulang dengan `-SkipSeed` agar email demo tidak dibuat dua kali.

Buka **http://127.0.0.1:8000**. MySQL tetap harus aktif.

| Role | Email | Password |
|---|---|---|
| Admin | admin@example.com | password |
| User | user@example.com | password |

Kedua akun demo tidak mengaktifkan 2FA. Akun baru dari register selalu `user`, termasuk
bila request dimanipulasi dengan `role=admin`. Password di-hash oleh model.

ZIP juga menyertakan `public/build` hasil kompilasi. Untuk memakai build tersebut,
setup bisa dijalankan dengan `-SkipNpm`; mode pengembangan dan build ulang tetap
memerlukan npm. `vendor` dan `node_modules` sengaja tidak disertakan.

## Setup manual (alternatif)

Buat database `hmvc_react` di phpMyAdmin jika tidak memakai script pembuat database.

```powershell
composer install --prefer-dist
Copy-Item .env.example .env  # hanya jika .env belum ada; sesuaikan koneksinya
php artisan key:generate
php artisan config:clear
php scripts/create-database.php
php artisan migrate --seed
php artisan storage:link
php artisan wayfinder:generate --with-form
npm.cmd ci
npm.cmd run types
npm.cmd run build
php artisan module:list
php artisan route:list --except-vendor
php artisan serve
```

Untuk development, jalankan `composer run dev`, atau dua terminal:

```powershell
# Terminal 1
php artisan serve
# Terminal 2
npm.cmd run dev
```

Script dev disesuaikan agar tidak memanggil Pail yang memerlukan `pcntl` pada Windows.
Worker queue tidak diperlukan untuk CRUD ini. Reset password memakai mailer `log`
bawaan `.env.example`; tautan email bisa dilihat di `storage/logs/laravel.log`.
Settings profil, password, appearance dan 2FA bawaan starter kit tetap tersedia.

## Isi dan perilaku aplikasi

| Bagian | Lokasi / URL | Perilaku |
|---|---|---|
| A | `config/modules.php`, `modules_statuses.json`, `Modules/*/composer.json` | nwidart 12, merge-plugin, dua modul Enabled, tanpa API Sanctum |
| A | `resources/js/lib/resolve-page.ts`, `app.tsx`, `ssr.tsx` | resolver bertipe untuk halaman utama dan `NamaModul::halaman` |
| A | `resources/css/app.css`, `tsconfig.json` | pemindaian Tailwind dan TypeScript mencakup modul |
| B | `app/Enums/UserRole.php`, `app/Models/User.php` | enum admin/user, default user, cast, `isAdmin`, `hasRole` |
| B | `app/Actions/Fortify/CreateNewUser.php`, `lang/id/auth.php` | register aman, pesan login Indonesia, throttle 5 kali/menit |
| B | `HandleInertiaRequests.php`, `use-shared-props.ts` | auth dan flash bersama, sidebar sesuai role |
| B | `/dashboard` | jumlah pendaftar, jumlah user hanya untuk admin |
| C | `app/Http/Middleware/EnsureUserHasRole.php`, `bootstrap/app.php` | alias role, tamu ke login, user mendapat 403 di admin |
| D | `Modules/UserManagement` → `/admin/users` | CRUD Inertia `useForm`, cari nama/email, filter role, pagination 10 |
| E | `Modules/Pendaftaran` → `/pendaftaran` | CRUD `fetch()` dan JSON; pencarian lima kolom di browser |
| F / 9.6 | `tests/Feature`, `docs/CHECKLIST-TAHAP-F.md` | tes bawaan auth/settings, tes modul dari PDF dan regresi tambahan |

UserManagement memakai model utama `App\Models\User`, bukan model modul baru.
Halaman modul disimpan di `Modules/<NamaModul>/resources/js/pages`.
`user-form.tsx` digunakan oleh create dan edit. Password kosong saat edit mempertahankan
password lama. Admin tidak bisa menurunkan role atau menghapus dirinya sendiri.

Pendaftaran menyimpan `nama`, `asal_sekolah`, `prodi_tujuan`, `email` unik, `no_hp`,
serta `berkas` dan `nama_berkas`. Nomor HP tetap string, regex sesuai PDF menerima
angka dan tanda `+` sepanjang 8–20 karakter. Prodi berupa input bebas sesuai praktikum.
Semua admin dan user dapat mengelola seluruh pendaftar sesuai matriks akses PDF.

Upload wajib saat tambah, opsional saat edit, jpg/jpeg/png/pdf maksimal **2048 KB**.
Nama fisik acak di `storage/app/public/berkas_pendaftaran`; nama asli dan path disimpan
di database. Tautan dibuka pada tab baru. Penggantian upload menghapus file lama;
menghapus data juga menghapus file. Berkas memakai disk public sesuai PDF, sehingga
tautan berkas dapat diakses siapa pun yang mengetahui URL-nya.

`resources/js/lib/http.ts` membaca cookie XSRF-TOKEN, mengirim X-XSRF-TOKEN,
Accept JSON, credentials same-origin; tidak memaksa Content-Type FormData. Edit upload
memakai POST dengan `_method=PUT`. Error 422 tampil di bawah input tanpa menutup dialog.
Konfirmasi hapus memakai shadcn Dialog dan pesan berhasil tertutup otomatis 4 detik.

## TypeScript dan halaman modul

Resolver bersama mengembalikan `Promise<ComponentType>` dari `PageModule.default`
dengan `import.meta.glob<PageModule>`. Tidak memakai `any`, `@ts-ignore`, atau cast
untuk menyembunyikan error `No overload matches this call`. Client dan SSR menggunakan
resolver yang sama. Alias `@/` tetap mengarah ke `resources/js`.

Blade root memuat entry `app.tsx` saja; entry itu memuat halaman melalui resolver.
Ini menghindari path salah `resources/js/pages/Pendaftaran::index.tsx` pada manifest.
Wayfinder route bawaan harus tersedia sebelum pemeriksaan TypeScript pada instalasi baru:

```powershell
php artisan wayfinder:generate --with-form
npm.cmd run types
npm.cmd run build
# opsional untuk SSR
npm.cmd run build:ssr
```

## Pengujian

```powershell
php artisan route:list
php artisan module:list
php artisan test
Get-ChildItem app,Modules,database,routes,config,tests,scripts -Recurse -Filter *.php |
    ForEach-Object { php -l $_.FullName }
npm.cmd run types
npm.cmd run build
```

`phpunit.xml` memakai SQLite `:memory:` agar tes tidak menghapus database MySQL Anda.
`RefreshDatabase` dan `Storage::fake('public')` dipakai pada tes. Baca
`docs/HASIL-UJI.md` untuk hasil nyata dan keterbatasan pengujian di lingkungan pembuat.
Untuk tes MySQL gunakan **database terpisah khusus tes**, jangan database berisi data
penting: RefreshDatabase akan membangun ulang tabel database pengujian.

## Pemecahan masalah

| Gejala | Perbaikan |
|---|---|
| `php/composer` tidak dikenal | Gunakan terminal Laragon atau masukkan folder PHP dan Composer ke PATH. |
| Script PowerShell diblokir | Gunakan perintah `powershell -ExecutionPolicy Bypass -File .\setup.ps1` di atas; tidak mengubah policy permanen. |
| MySQL connection refused / access denied | Start MySQL, cocokkan host, port, user dan password `.env`, lalu `php artisan config:clear`. |
| Email seed sudah ada | Jalankan ulang setup dengan `-SkipSeed`; jangan memakai migrate:fresh kecuali hendak menghapus seluruh data. |
| `Class Modules... not found` | Pastikan `Modules` dan `modules_statuses.json` ikut diekstrak, lalu `composer dump-autoload`. |
| Halaman tidak ditemukan | Cocokkan huruf besar nama modul dan file `.tsx` dengan controller. |
| Vite manifest tidak ditemukan | `npm.cmd ci` kemudian `npm.cmd run build`. |
| `@/routes` tidak ditemukan | `php artisan wayfinder:generate --with-form` lalu `npm.cmd run types`. |
| Browser mencoba localhost:5173 yang mati | Hapus hanya `public/hot` yang tersisa dari dev server, atau jalankan lagi `npm.cmd run dev`. |
| 419 / session kedaluwarsa | Muat ulang, login lagi, gunakan host konsisten (`127.0.0.1`), jangan mencampur localhost. |
| 422 upload | Cek field, duplikasi email, format dan ukuran; sesuaikan PHP `upload_max_filesize` dan `post_max_size` minimal 4M/8M. |
| Berkas 404 | `php artisan storage:link`; bila Windows menolak symlink aktifkan Developer Mode atau jalankan terminal elevated untuk perintah itu. |
| CSS modul tidak tampil | Pastikan `@source` modul ada di app.css lalu build ulang. |

## Batas ruang lingkup tugas akhir modul

PDF 9.8 halaman 109–110 adalah **tugas aplikasi baru SiPustaka**, dengan project
`sipustaka_hmvc_react`, database `db_sipustaka`, modul Buku/Peminjaman/UserManagement,
serta adaptasi tugas akhir sesuai tema mahasiswa. Itu bukan perubahan pada praktikum
Pendaftaran Tahap A–F. ZIP ini menyelesaikan praktikum yang diminta, bukan mengklaim
menyelesaikan SiPustaka atau tugas akhir bertema pribadi. Rincian pemetaan dan kebutuhan
laporan 9.9 dicatat di `docs/PEMETAAN-PDF.md` dan checklist dokumentasi.

Sumber starter kit dan commit terdapat di `UPSTREAM.md`. Dependency terkunci dalam
`composer.lock` dan `package-lock.json`; gunakan `install/ci`, bukan `update`, untuk
mengulang versi yang diuji. Ganti password akun demo sebelum dipakai di luar praktikum.
