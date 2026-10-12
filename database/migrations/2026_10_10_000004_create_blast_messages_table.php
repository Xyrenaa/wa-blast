<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Satu baris = satu pesan ke satu penerima (dipakai juga sebagai delivery log)
        Schema::create('blast_messages', function (Blueprint $t) {
            $t->id();
            $t->foreignId('blast_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $t->string('nama');
            $t->string('no_wa', 20)->nullable();
            $t->text('pesan'); // pesan final setelah variabel diganti
            $t->string('status', 20)->default('pending'); // pending|sent|failed
            $t->string('error')->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamps();
            $t->index(['blast_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blast_messages');
    }
};
