<?php

namespace App\Http\Controllers;

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

        // 2. Calcular Nivel
        $level = floor($user->score / 100) + 1;
        $nextLevelPoints = $level * 100;
        $progressToNext = $user->score % 100;

        // 3. Obtener historial reciente (🌟 Optimizado)
        $history = UserProgress::with('clip') 
            ->where('user_id', $user->id)
            ->where('watched', true)
            ->latest('viewed_at')
            ->take(5)
            ->get()
            ->map(function ($progress) {
                // 🌟 PROTECCIÓN DE RENDIMIENTO: Evitamos que Clip intente calcular likes_count
                $clip = $progress->clip->setAppends(['thumbnail_url']); 

                return [
                    'id' => $progress->id, 
                    'clip_id' => $clip->id, 
                    'title' => $clip->title, 
                    'thumbnail_url' => $clip->thumbnail_url, 
                    'score' => $progress->answered_correctly ? 'Acertado' : 'Fallado',
                    'date_human' => Carbon::parse($progress->viewed_at)->diffForHumans(), 
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