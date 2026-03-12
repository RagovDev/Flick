<?php

namespace App\Http\Controllers;

use App\Models\Clip;
use App\Models\User;
use App\Models\UserProgress;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. Estadísticas Globales
        $stats = [
            'total_clips' => Clip::count(),
            'total_users' => User::count(),
            'total_answers' => UserProgress::count(),
        ];

        // 2. Conteo por Categoría
        // Esto agrupa los clips y cuenta cuántos hay en cada canal
        $categories = Clip::select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->get();

        // 3. Últimos videos subidos (para tener acceso rápido)
        // 🌟 Tip Pro: latest() es más limpio y legible que orderBy('created_at', 'desc')
        $recentClips = Clip::latest()
            ->take(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'categories' => $categories,
            'recentClips' => $recentClips
        ]);
    }
}