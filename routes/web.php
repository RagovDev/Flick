<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\ClipController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminClipController;
use App\Http\Controllers\VocabularyController;
use App\Http\Controllers\AdminDashboardController;

Route::post('/flick/like/{clip}', [ClipController::class, 'toggleLike'])->name('flick.like')->middleware('auth');

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function (){
    
    # -----------------------------------
    #   ZONA RESTRINGIDA (Solo Admins) 🚨
    # -----------------------------------
    Route::middleware(['role:admin'])->group(function () {
        // Formulario de creación
        Route::get('/admin/clips/upload', [AdminClipController::class, 'create'])->name('admin.clips.create');
        // Guardar datos
        Route::post('/admin/clips/upload', [AdminClipController::class, 'store'])->name('admin.clips.store');  
        // Dashboard estadistico administrativo
        Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    });

    # -----------------------------------
    #   Dashboard General
    # -----------------------------------
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    # -----------------------------------
    #   Flick (Reproductor)
    # -----------------------------------
    Route::get('/flick', [ClipController::class, 'index'])->name('flick.index');
    Route::get('/flick/next', [ClipController::class, 'getNext'])->name('flick.next');
    Route::post('/flick/check', [ClipController::class, 'check'])->name('flick.check');
    Route::get('flick/watch/{id}', [ClipController::class, 'show'])->name('flick.show');
    Route::post('/flick/like/{clip}', [ClipController::class, 'toggleLike'])->name('flick.like');

    # -----------------------------------
    #   Vocabulario
    # -----------------------------------
    Route::post('/vocabulary/save', [VocabularyController::class, 'store'])->name('vocabulary.save');
    Route::get('/vocabulary', [VocabularyController::class, 'index'])->name('vocabulary.index');
    Route::get('/vocabulary/practice', [VocabularyController::class, 'practice'])->name('vocabulary.practice');

});

require __DIR__.'/settings.php';