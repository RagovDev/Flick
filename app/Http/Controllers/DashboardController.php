<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

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
        // floor() redondea hacia abajo. Si tienes 250 ptos -> Nivel 2.
        $level = floor($user->score / 100) + 1;
        
        // Puntos para el siguiente nivel
        $nextLevelPoints = $level * 100;
        $progressToNext = $user->score % 100; // El residuo es el porcentaje (si el nivel es de 100 en 100)

        // 3. Obtener historial reciente (últimos 5 videos vistos)
        $history = UserProgress::with('clip')
            ->where('user_id', $user->id)
            ->where('watched', true)
            ->latest('viewed_at')
            ->take(5)
            ->get()
            ->map(function ($progress) {
                return [
                    'id' => $progress->clip->id,
                    'title' => $progress->clip->title,
                    'thumbnail' => '/storage/thumbnails/' . $progress->clip->id . '.jpg', // Placeholder
                    'score' => $progress->answered_correctly ? 'Acertado' : 'Visto',
                    'date' => $progress->viewed_at, // Carbon instance
                ];
            });

        return Inertia::render('Dashboard', [
            'stats' => [
                'level' => $level,
                'total_watched' => $totalWatched,
                'next_level_points' => $nextLevelPoints,
                'progress_percent' => $progressToNext,
            ],
            'history' => $history
        ]);
    }
}
