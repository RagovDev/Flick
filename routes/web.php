<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\ClipController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminClipController;
use App\Http\Controllers\VocabularyController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\GoogleAuthController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

# -----------------------------------
#   Rutas Públicas (Autenticación)
# -----------------------------------
Route::controller(GoogleAuthController::class)->group(function () {
    Route::get('/auth/google', 'redirect')->name('google.login');
    Route::get('/auth/google/callback', 'callback');
});

// Rutas protegidas
Route::middleware(['auth', 'verified'])->group(function () {

    # -----------------------------------
    #   ZONA RESTRINGIDA (Solo Admins) 🚨
    # -----------------------------------
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        
        Route::controller(AdminClipController::class)->prefix('clips')->name('clips.')->group(function () {
            Route::get('/upload', 'create')->name('create');
            Route::post('/upload', 'store')->name('store');
        });
    });

    # -----------------------------------
    #   Dashboard General
    # -----------------------------------
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    # -----------------------------------
    #   Flick (Reproductor)
    # -----------------------------------
    Route::controller(ClipController::class)->prefix('flick')->name('flick.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/next', 'getNext')->name('next');
        Route::post('/check', 'check')->name('check');
        Route::get('/watch/{id}', 'show')->name('show');
        Route::post('/like/{clip}', 'toggleLike')->name('like');
        Route::post('/translate', 'translateWord')->name('translate');
    });

    # -----------------------------------
    #   Vocabulario
    # -----------------------------------
    Route::controller(VocabularyController::class)->prefix('vocabulary')->name('vocabulary.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/practice', 'practice')->name('practice');
        Route::post('/save', 'store')->name('store'); // 🌟 Bug corregido: ahora se llama 'store'
        Route::delete('/{id}', 'destroy')->name('destroy');
    });
});

require __DIR__ . '/settings.php';
