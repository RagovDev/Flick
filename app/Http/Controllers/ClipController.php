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
     * Carga el primer video disponible para Inertia.
     */
    public function index()
    {
        // Reutilizamos la lógica de obtener un video
        $clip = $this->fetchNextClip();

        if (!$clip) {
            // Si no hay videos, mostramos una pantalla de "Todo completado"
            return Inertia::render('Completed'); 
        }

        return Inertia::render('Player', [
            'initialClip' => $clip
        ]);
    }

    /**
     * API Endpoint: Devuelve el siguiente video en formato JSON.
     * Usado por Vue.js para cargar el siguiente video en segundo plano (infinite scroll).
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
            ->inRandomOrder() // Para que no sea aburrido
            ->first();
    }

    /**
     * Valida la respuesta del usuario.
     */
    public function checkAnswer(Request $request)
    {
        $request->validate([
            'clip_id' => 'required|exists:clips,id',
            'option_id' => 'required|exists:options,id',
        ]);

        $user = Auth::user();
        $option = Option::find($request->option_id);
        
        // Verificar si es correcta
        $isCorrect = $option->is_correct;

        // Guardar progreso (Evitamos duplicados con firstOrCreate o updateOrCreate)
        UserProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'clip_id' => $request->clip_id
            ],
            [
                'watched' => true,
                'answered_correctly' => $isCorrect,
                'viewed_at' => now()
            ]
        );

        // AQUÍ PODRÍAS SUMAR PUNTOS AL USUARIO EN EL FUTURO
        // if ($isCorrect) { $user->increment('points', 10); }

        return response()->json([
            'correct' => $isCorrect,
            'correct_option_id' => $isCorrect ? null : Option::where('question_id', $option->question_id)->where('is_correct', true)->value('id'),
            'message' => $isCorrect ? '¡Correcto!' : 'Ups, casi.'
        ]);
    }
}