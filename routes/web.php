<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\ClipController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth', 'verified'])->prefix('flick')->name('flick.')->group(function () {
    // 1. La página principal donde vive la app
    Route::get('/', [ClipController::class, 'index'])->name('index');

    // 2. Endpoint para pedir el siguiente video (AJAX/Fetch)
    Route::get('/next', [ClipController::class, 'getNext'])->name('next');

    // 3. Endpoint para enviar la respuesta
    Route::post('/check', [ClipController::class, 'checkAnswer'])->name('check');
});


require __DIR__.'/settings.php';


use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

// Esta ruta fuerza a Laravel a servir el archivo correctamente
// Route::get('/video-stream/{filename}', function ($filename) {
//     // Busca en storage/app/public/videos/
//     $path = "videos/{$filename}";
    
//     if (!Storage::disk('public')->exists($path)) {
//         abort(404);
//     }

//     $file = Storage::disk('public')->path($path);

//     // Devuelve el archivo preparado para streaming (Range support)
//     return response()->file($file);
// })->where('filename', '.*');
