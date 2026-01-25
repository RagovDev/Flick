<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Clip;
use App\Models\Option;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ClipController extends Controller
{
    /**
     * Muestra la vista principal (El reproductor).
     */
    public function index()
    {
        $clip = $this->fetchNextClip();

        if (!$clip) {
            // Si ya vio todo, lo mandamos al Dashboard para que vea su trofeo
            return redirect()->route('dashboard'); 
        }

        // CORRECCIÓN IMPORTANTE:
        // Pasamos 'userScore' para que el HUD muestre los puntos al cargar la página.
        return Inertia::render('Player', [ 
            'initialClip' => $clip,
            'userScore' => Auth::user()->score, 
        ]);
    }

    /**
     * API Endpoint: Devuelve el siguiente video en formato JSON.
     */
    public function getNext()
    {
        $clip = $this->fetchNextClip();

        if (!$clip) {
            return response()->json(['message' => 'No more clips'], 204);
        }

        return response()->json($clip);
    }

    /**
     * Valida la respuesta del usuario y suma puntos.
     * (Renombrado a 'check' para coincidir con routes/web.php)
     */
    public function check(Request $request)
    {
        $request->validate([
            'clip_id' => 'required|exists:clips,id',
            'option_id' => 'required|exists:options,id',
        ]);

        $user = Auth::user();
        
        // Verificar si ya respondió este video antes (para no sumar puntos dobles)
        $alreadyAnswered = UserProgress::where('user_id', $user->id)
            ->where('clip_id', $request->clip_id)
            ->exists();

        $option = Option::find($request->option_id);
        $isCorrect = $option->is_correct;

        // Guardar o Actualizar progreso
        UserProgress::updateOrCreate(
            ['user_id' => $user->id, 'clip_id' => $request->clip_id],
            [
                'watched' => true,
                'answered_correctly' => $isCorrect,
                'viewed_at' => now()
            ]
        );

        // LÓGICA DE PUNTOS:
        $pointsEarned = 0;
        
        // Solo sumamos si es correcta Y si es la primera vez que responde este video
        if ($isCorrect && !$alreadyAnswered) {
            // Buscamos cuántos puntos vale la pregunta asociada (o 10 por defecto)
            $questionPoints = $option->question->points ?? 10; 
            
            $user->increment('score', $questionPoints);
            $pointsEarned = $questionPoints;
        }

        return response()->json([
            'correct' => $isCorrect,
            'points_earned' => $pointsEarned,
            'total_score' => $user->fresh()->score, // Devolvemos el puntaje actualizado para que Vue lo lea
            'message' => $isCorrect ? '¡Correcto!' : 'Ups, casi.'
        ]);
    }

    /**
     * Lógica privada para buscar un video no visto.
     */
    private function fetchNextClip()
    {
        $userId = Auth::id();

        return Clip::with(['questions.options']) // Cargamos preguntas y opciones
            ->whereDoesntHave('userProgress', function ($query) use ($userId) {
                // Filtro: Donde NO exista un registro de progreso para este usuario
                $query->where('user_id', $userId);
            })
            ->inRandomOrder() 
            ->first();
    }
}