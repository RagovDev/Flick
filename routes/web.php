<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\ClipController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminClipController;

// Grupo de rutas para Admin
Route::middleware(['auth', 'verified'])->prefix('admin')->group(function () {
    
    // Formulario de creación
    Route::get('/upload', [AdminClipController::class, 'create'])->name('admin.create');
    
    // Guardar datos
    Route::post('/upload', [AdminClipController::class, 'store'])->name('admin.store');
});

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Route::get('dashboard', function () {
//     return Inertia::render('Dashboard_old');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


Route::middleware(['auth', 'verified'])->prefix('flick')->name('flick.')->group(function () {
    // 1. La página principal donde vive la app
    Route::get('/', [ClipController::class, 'index'])->name('index');

    // 2. Endpoint para pedir el siguiente video (AJAX/Fetch)
    Route::get('/next', [ClipController::class, 'getNext'])->name('next');

    // 3. Endpoint para enviar la respuesta
    Route::post('/check', [ClipController::class, 'check'])->name('check');
});


require __DIR__.'/settings.php';

// Route::get('/debug-php', function () {
//     return [
//         'php_ini_path' => php_ini_loaded_file(), // ¿Qué archivo estás leyendo?
//         'post_max_size' => ini_get('post_max_size'), // ¿Cuánto permites subir?
//         'upload_max_filesize' => ini_get('upload_max_filesize'), // ¿De qué tamaño puede ser el archivo?
//     ];
// });