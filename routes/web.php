<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WhatsAppController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\BlastController;

   Route::get('/', fn () => auth()->check()
       ? redirect()->route('dashboard')
       : redirect()->route('login'));

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/wa/status', [WhatsAppController::class, 'status'])->name('wa.status');
    Route::post('/wa/logout', [WhatsAppController::class, 'logout'])->name('wa.logout');
    Route::get('/students/template', [StudentController::class, 'template'])->name('students.template');
    Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
    Route::resource('students', StudentController::class)->except(['show']);
    Route::resource('templates', MessageTemplateController::class)->except(['create', 'show']);
    Route::get('/blasts', [BlastController::class, 'index'])->name('blasts.index');
    Route::get('/blasts/create', [BlastController::class, 'create'])->name('blasts.create');
    Route::get('/blasts/recipients', [BlastController::class, 'recipients'])->name('blasts.recipients');
    Route::post('/blasts', [BlastController::class, 'store'])->name('blasts.store');
    Route::get('/blasts/{blast}', [BlastController::class, 'show'])->name('blasts.show');
    Route::post('/blasts/{blast}/cancel', [BlastController::class, 'cancel'])->name('blasts.cancel');
    Route::post('/blasts/{blast}/resume', [BlastController::class, 'resume'])->name('blasts.resume');
    Route::get('/blasts/{blast}/export', [BlastController::class, 'export'])->name('blasts.export');
    Route::post('/blasts/{blast}/retry', [BlastController::class, 'retryFailed'])->name('blasts.retry');
});

require __DIR__.'/auth.php';