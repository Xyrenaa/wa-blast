<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $t) {
            $t->id();
            $t->string('nim', 30)->unique();
            $t->string('nama');
            $t->unsignedSmallInteger('angkatan')->index();
            $t->string('jurusan')->index();
            $t->string('no_wa', 20)->nullable(); // boleh kosong, nanti ditandai "nomor tidak valid"
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
