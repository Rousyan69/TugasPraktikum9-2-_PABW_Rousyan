# Checklist demonstrasi Tahap F dan laporan

Daftar ini untuk demonstrasi browser pada mesin mahasiswa; tanda di bawah bukan klaim
bahwa tangkapan layar sudah diambil. Hasil tes otomatis tercatat terpisah di HASIL-UJI.md.

- [ ] Tamu membuka `/pendaftaran` dan `/admin/users`: dialihkan ke login.
- [ ] Register password pendek/konfirmasi salah: error per input, nama/email tetap.
- [ ] Register benar: langsung login, role user, tanpa menu Kelola User.
- [ ] Logout dari menu bawah sidebar: kembali ke awal dan halaman privat tertutup.
- [ ] Login salah: pesan Indonesia; percobaan lebih dari lima kali dibatasi satu menit.
- [ ] User mengetik `/admin/users`: HTTP 403.
- [ ] Admin login: menu Kelola User dan kartu jumlah user muncul.
- [ ] Tambah user kosong: validasi; tambah akun admin: redirect dengan flash berhasil.
- [ ] Edit user dengan password kosong: data berubah, password lama tetap berlaku.
- [ ] Cari nama/email, filter role, pagination lebih dari 10 data bekerja.
- [ ] Hapus user: konfirmasi dulu; batal tidak menghapus; sukses muncul flash.
- [ ] Edit role sendiri ke user ditolak; hapus diri sendiri ditolak di server.
- [ ] Pendaftaran awal menampilkan kosong dan jumlah data 0.
- [ ] DevTools Network → Fetch/XHR: simpan kosong menghasilkan POST 422 tanpa pindah halaman.
- [ ] Tambah lima field + jpg/jpeg/png/pdf di bawah 2 MB: dialog tertutup, tabel diperbarui.
- [ ] Klik nama file: tab baru `/storage/berkas_pendaftaran/...` terbuka.
- [ ] File zip / file lebih dari 2 MB ditolak di bawah input berkas.
- [ ] Cari kelima kolom secara instan: tidak ada request baru di Network.
- [ ] Edit mengambil GET `/{id}/edit`; file lama terlihat, upload kosong mempertahankan file.
- [ ] Edit dengan file pengganti: POST berisi `_method=PUT`, file lama hilang dari storage.
- [ ] Hapus pendaftar: DELETE setelah konfirmasi, file dan data hilang, badge berkurang.
- [ ] `/pendaftaran/data` menampilkan JSON; ID tidak ada menghasilkan 404.
- [ ] phpMyAdmin: path di kolom berkas, nama asli di nama_berkas; isi file tetap di storage.
- [ ] `php artisan route:list`, `module:list`, `test` dan build berhasil.

Untuk laporan 9.9, tambahkan identitas/cover/logo institusi milik mahasiswa, tujuan dengan
kalimat sendiri, alat dan versi mesin sendiri, langkah A–F, tangkapan layar bernomor serta
keterangan, analisis HMVC/Inertia/AJAX/CSRF/FormData, kesimpulan, link GitHub milik
mahasiswa dan demonstrasi. Jangan memakai screenshot atau data identitas rekaan.
