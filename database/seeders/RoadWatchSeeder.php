<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Modules\LaporanJalan\Models\LaporanJalan;
use Modules\RuasJalan\Models\RuasJalan;

class RoadWatchSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(['email' => 'admin@roadwatch.test'], ['name' => 'Admin Desa', 'role' => 'admin', 'password' => 'RoadWatch123!', 'email_verified_at' => now()]);
        $warga = User::firstOrCreate(['email' => 'warga@roadwatch.test'], ['name' => 'Warga Citeureup', 'role' => 'warga', 'password' => 'RoadWatch123!', 'email_verified_at' => now()]);
        foreach (['Jalan Desa Citeureup', 'Jalan Pasar', 'Jalan Sekolah', 'Jalan Sawah', 'Jalan Balai Desa'] as $i => $nama) {
            $ruas = RuasJalan::firstOrCreate(['nama' => $nama], ['lokasi' => 'RW '.($i + 1).', Desa Citeureup', 'panjang' => 250 + 50 * $i, 'lebar' => 4, 'kondisi' => 'Rusak Ringan']);
            $judul = 'Kerusakan permukaan '.$nama;
            if (! LaporanJalan::where('judul', $judul)->exists()) {
                $path = 'roadwatch/seed-'.($i + 1).'.png';
                Storage::disk('local')->put($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
                LaporanJalan::create(['ruas_jalan_id' => $ruas->id, 'user_id' => $warga->id, 'judul' => $judul, 'deskripsi' => 'Data demonstrasi: permukaan jalan berlubang dan perlu pemeriksaan petugas. Foto contoh berupa gambar PNG satu piksel.', 'tanggal' => today()->subDays($i)->toDateString(), 'status' => 'Diajukan', 'foto' => $path]);
            }
        }
    }
}
