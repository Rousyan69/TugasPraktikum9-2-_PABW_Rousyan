<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ruas_jalans', function (Blueprint $t) {
            $t->id();
            $t->string('nama', 150);
            $t->string('lokasi');
            $t->decimal('panjang', 10, 2);
            $t->decimal('lebar', 6, 2);
            $t->string('kondisi', 30);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruas_jalans');
    }
};
