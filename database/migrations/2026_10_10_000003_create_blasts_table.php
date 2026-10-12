<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blasts', function (Blueprint $t) {
            $t->id();
            $t->string('judul');
            $t->foreignId('message_template_id')->nullable()->constrained()->nullOnDelete();
            $t->text('isi_pesan'); // salinan isi pesan saat blast dibuat
            $t->json('filter')->nullable(); // {"angkatan":2024,"jurusan":"Sistem Informasi"} atau {"source":"excel"}
            $t->string('status', 20)->default('draft')->index(); // draft|scheduled|queued|running|done|cancelled
            $t->timestamp('scheduled_at')->nullable()->index();
            $t->timestamp('started_at')->nullable();
            $t->timestamp('finished_at')->nullable();
            $t->unsignedInteger('total')->default(0);
            $t->unsignedInteger('sent_count')->default(0);
            $t->unsignedInteger('failed_count')->default(0);
            $t->unsignedTinyInteger('delay_min')->default(5);  // detik
            $t->unsignedTinyInteger('delay_max')->default(15); // detik
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blasts');
    }
};
