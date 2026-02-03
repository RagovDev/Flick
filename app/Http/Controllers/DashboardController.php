<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Calcular Estadísticas Básicas
        $totalWatched = UserProgress::where('user_id', $user->id)
            ->where('watched', true)
            ->count();

        // 2. Calcular Nivel (Ejemplo: Cada 100 puntos subes de nivel)
        $level = floor($user->score / 100) + 1;
        
        // Puntos para el siguiente nivel
        $nextLevelPoints = $level * 100;
        $progressToNext = $user->score % 100;

        // 3. Obtener historial reciente
        $history = UserProgress::with('clip') // Cargamos la relación 'clip' para acceder a sus datos
            ->where('user_id', $user->id)
            ->where('watched', true)
            ->latest('viewed_at')
            ->take(5)
            ->get()
            ->map(function ($progress) {
                return [
                    'id' => $progress->id, 
                    'clip_id' => $progress->clip->id, 
                    'title' => $progress->clip->title, 
                    'thumbnail_url' => $progress->clip->thumbnail_url, // CORRECTO: Llamamos al Accessor del Modelo.
                    'score' => $progress->answered_correctly ? 'Acertado' : 'Fallado', // Ajusté 'Visto' a 'Fallado' si prefieres esa lógica, o déjalo como 'Visto'
                    'date_human' => \Carbon\Carbon::parse($progress->viewed_at)->diffForHumans(), // Usamos Carbon::parse() para convertir el texto a fecha real
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => [
                'level' => $level,
                'progress_percent' => $progressToNext,
                'next_level_points' => $nextLevelPoints,
                'total_watched' => $totalWatched,
            ],
            'history' => $history
        ]);
    }
}
