<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $t) {
            $t->id();
            $t->string('nama');
            $t->string('kategori', 50)->nullable();
            $t->text('isi'); // berisi variabel, mis. {nama_mahasiswa}
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
