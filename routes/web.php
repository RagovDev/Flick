<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\ClipController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminClipController;
use App\Http\Controllers\VocabularyController;


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');


Route::middleware(['auth', 'verified'])->group(function (){
    # -----------------------------------
    #   Admin
    # -----------------------------------

    // Formulario de creación
    Route::get('/admin/upload', [AdminClipController::class, 'create'])->name('admin.create');
    
    // Guardar datos
    Route::post('/admin/upload', [AdminClipController::class, 'store'])->name('admin.store');

    # -----------------------------------
    #   Dashboard
    # -----------------------------------

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    # -----------------------------------
    #   Flick (Reproductor)
    # -----------------------------------

    // La página principal donde vive la app
    Route::get('/flick', [ClipController::class, 'index'])->name('flick.index');

    // 2Endpoint para pedir el siguiente video (AJAX/Fetch)
    Route::get('/flick/next', [ClipController::class, 'getNext'])->name('flick.next');

    // Endpoint para enviar la respuesta
    Route::post('/flick/check', [ClipController::class, 'check'])->name('flick.check');

    // Para ver un video especifico (replay)
    Route::get('flick/watch/{id}', [ClipController::class, 'show'])->name('flick.show');

    # -----------------------------------
    #   Vocabulario
    # -----------------------------------

    // Ruta para guardar palabras desde el video
    Route::post('/vocabulary/save', [VocabularyController::class, 'store'])->name('vocabulary.save');

    // Ruta para ver mis palabras (Flashcards)
    Route::get('/vocabulary', [VocabularyController::class, 'index'])->name('vocabulary.index');

    // Ruta modo practica
    Route::get('/vocabulary/practice', [VocabularyController::class, 'practice'])->name('vocabulary.practice');

});

require __DIR__.'/settings.php';

// Route::get('/debug-php', function () {
//     return [
//         'php_ini_path' => php_ini_loaded_file(), // ¿Qué archivo estás leyendo?
//         'post_max_size' => ini_get('post_max_size'), // ¿Cuánto permites subir?
//         'upload_max_filesize' => ini_get('upload_max_filesize'), // ¿De qué tamaño puede ser el archivo?
//     ];
// });