<?php

namespace App\Http\Controllers;

// Eliminamos "use App\Http\Controllers\Controller;" porque ya estamos en ese namespace
use App\Models\Clip;
use App\Models\Option;
use App\Models\UserProgress;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ClipController extends Controller
{
    /**
     * Muestra la vista principal (El reproductor).
     */
    public function index(Request $request)
    {
        // 🌟 Usamos nuestro nuevo motor de búsqueda centralizado
        $clip = $this->findNextClip($request);

        if (!$clip) {
            return redirect()->route('dashboard')->with('error', 'No hay videos en esa categoría aún.');
        }

        return Inertia::render('Player', [
            'initialClip' => $clip,
            'activeCategory' => $request->category ?? null
        ]);
    }

    /**
     * API Endpoint: Devuelve el siguiente video en formato JSON.
     */
    public function getNext(Request $request)
    {
        // 🌟 Usamos el mismo motor aquí
        $clip = $this->findNextClip($request);

        if (!$clip) {
            return response()->json(['message' => 'No more clips'], 204);
        }

        return response()->json($clip);
    }

    /**
     * Valida la respuesta del usuario y suma puntos.
     */
    public function check(Request $request)
    {
        $request->validate([
            'clip_id' => 'required|exists:clips,id',
            'option_id' => 'required|exists:options,id',
        ]);

        $user = Auth::user();

        $alreadyAnswered = UserProgress::where('user_id', $user->id)
            ->where('clip_id', $request->clip_id)
            ->exists();

        // Seguridad Backend: Si ya respondió, rechazamos el intento de sumar puntos
        if ($alreadyAnswered) {
            return response()->json([
                'correct' => false,
                'points_earned' => 0,
                'total_score' => $user->score,
                'message' => 'Ya respondiste este video.'
            ]);
        }

        $option = Option::find($request->option_id);
        $isCorrect = $option->is_correct;

        UserProgress::updateOrCreate(
            ['user_id' => $user->id, 'clip_id' => $request->clip_id],
            [
                'watched' => true,
                'answered_correctly' => $isCorrect,
                'viewed_at' => now()
            ]
        );

        $pointsEarned = 0;

        if ($isCorrect) {
            $questionPoints = $option->question->points ?? 10;
            $user->increment('score', $questionPoints);
            $pointsEarned = $questionPoints;
        }

        return response()->json([
            'correct' => $isCorrect,
            'points_earned' => $pointsEarned,
            'total_score' => $user->fresh()->score,
            'message' => $isCorrect ? '¡Correcto!' : 'Ups, casi.'
        ]);
    }

    /**
     * Muestra un video específico para repaso (Modo Práctica).
     */
    public function show($id)
    {
        $clip = Clip::with('questions.options')->findOrFail($id);

        return Inertia::render('Player', [
            'initialClip' => $clip,
            'userScore' => Auth::user()->score,
            'isPracticeMode' => true,
        ]);
    }

    /**
     * Funcion para que el usuario pueda dar like a un video
     */
    public function toggleLike($clipId)
    {
        try {
            $userId = Auth::id();

            $like = Like::where('user_id', $userId)
                ->where('clip_id', $clipId)
                ->first();

            if ($like) {
                $like->delete();
                return response()->json(['status' => 'unliked']);
            }

            Like::create([
                'user_id' => $userId,
                'clip_id' => $clipId
            ]);

            return response()->json(['status' => 'liked']);
        } catch (\Exception $e) {
            Log::error("Error en Like: " . $e->getMessage());
            return response()->json(['error' => 'No se pudo guardar'], 500);
        }
    }

    /**
     * Traduce una palabra basándose en el contexto del subtítulo usando Gemini.
     */
    public function translateWord(Request $request)
    {
        $request->validate([
            'word' => 'required|string',
            'context' => 'required|string',
        ]);

        $apiKey = env('GEMINI_API_KEY');

        $prompt = "
        Eres un diccionario bilingüe experto (Inglés a Español). 
        Tu tarea es traducir UNA sola palabra basándote en el contexto en el que se usa.

        Palabra objetivo: '{$request->word}'
        Contexto de la frase: '{$request->context}'

        REGLAS ESTRICTAS E INQUEBRANTABLES:
        1. Traduce ÚNICAMENTE la 'Palabra objetivo'.
        2. Usa el contexto SOLO para entender qué significado aplica, PERO PROHIBIDO traducir toda la frase.
        3. La traducción debe ser muy concisa (máximo 1 a 3 palabras).
        4. Devuelve ÚNICAMENTE un objeto JSON válido, sin texto adicional, sin formato markdown (sin ```json).
        5. La 'phonetic' debe ser la pronunciación en INGLÉS utilizando el Alfabeto Fonético Internacional (IPA).

        Usa exactamente esta estructura:
        {
            \"translation\": \"tu_traduccion_corta_aqui\",
            \"phonetic\": \"/pronunciacion_en_IPA/\"
        }
        ";

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=" . $apiKey, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            if ($response->successful()) {
                $aiText = $response->json('candidates.0.content.parts.0.text');
                $aiText = preg_replace('/```json|```/', '', $aiText);
                return response()->json(json_decode(trim($aiText), true));
            } else {
                return response()->json([
                    'translation' => 'Error de Google',
                    'phonetic' => '',
                    'debug_google' => env('APP_DEBUG') ? $response->json() : null // Protege info en prod
                ], 500);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'translation' => 'Error de conexión',
                'phonetic' => '',
                'debug_laravel' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }

    // ==========================================
    // 🛠️ MÉTODOS PRIVADOS (Helpers)
    // ==========================================

    /**
     * Motor centralizado para buscar el siguiente clip.
     * Reutilizado por index() y getNext().
     */
    private function findNextClip(Request $request)
    {
        // 1. Preparamos la consulta base con sus relaciones
        $query = Clip::with(['questions.options'])
            ->withCount('likes')
            ->withExists(['likes' => function ($q) {
                $q->where('user_id', Auth::id());
            }]);

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // 2. Buscamos qué videos ya vio el usuario
        $watchedIds = UserProgress::where('user_id', Auth::id())
            ->where('watched', true)
            ->pluck('clip_id');

        // 3. Intentamos traer un video nuevo
        // Clonamos la query para no afectarla si necesitamos usar el fallback
        $clip = (clone $query)->whereNotIn('id', $watchedIds)
            ->inRandomOrder()
            ->first();

        // 4. Fallback: Si ya vio todos, le mostramos uno al azar
        if (!$clip) {
            $clip = $query->inRandomOrder()->first();
        }

        // 5. Inyectamos su estado de progreso antes de devolverlo
        if ($clip) {
            $this->attachUserProgress($clip);
        }

        return $clip;
    }

    /**
     * Adjunta el estado (completado/ganado) al objeto clip
     */
    private function attachUserProgress($clip)
    {
        $progress = UserProgress::where('user_id', Auth::id())
            ->where('clip_id', $clip->id)
            ->first();

        $clip->completed = $progress ? true : false;
        $clip->won = $progress ? (bool)$progress->answered_correctly : false;
    }
}