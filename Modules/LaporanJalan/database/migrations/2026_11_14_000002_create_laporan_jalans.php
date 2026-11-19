<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laporan_jalans', function (Blueprint $t) {
            $t->id();
            $t->foreignId('ruas_jalan_id')->constrained('ruas_jalans')->restrictOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('judul', 150);
            $t->text('deskripsi');
            $t->date('tanggal');
            $t->string('status', 30)->default('Diajukan');
            $t->string('foto');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_jalans');
    }
};
