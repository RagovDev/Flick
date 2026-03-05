<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Clip;
use App\Models\Option;
use App\Models\UserProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Like;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ClipController extends Controller
{
    /**
     * Muestra la vista principal (El reproductor).
     */
    public function index(Request $request)
    {
        // 1. PREPARAR QUERY (Siempre con preguntas)
        $query = Clip::with(['questions.options'])
            ->withCount('likes')
            ->withExists(['likes' => function ($query) {
                $query->where('user_id', Auth::id());
            }]);

        // 2. FILTRO DE CATEGORÍA
        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        // 3. BUSCAR VIDEO NO VISTO
        $watchedIds = UserProgress::where('user_id', Auth::id())
            ->where('watched', true)
            ->pluck('clip_id');

        $clip = $query->whereNotIn('id', $watchedIds)
            ->inRandomOrder()
            ->first();

        // 4. FALLBACK: Si ya vio todo, repetimos (pero cargando preguntas)
        if (!$clip) {
            $query = Clip::with(['questions.options'])
                ->withCount('likes')
                ->withExists(['likes' => function ($query) {
                    $query->where('user_id', Auth::id());
                }]);

            if ($request->has('category') && $request->category !== 'all') {
                $query->where('category', $request->category);
            }
            $clip = $query->inRandomOrder()->first();
        }

        // 5. ERROR: Si no hay videos
        if (!$clip) {
            return redirect()->route('dashboard')->with('error', 'No hay videos en esa categoría aún.');
        }

        // 6. INYECCIÓN DE ESTADO
        $this->attachUserProgress($clip);

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
        // Función anónima para no repetir la lógica de carga de relaciones y likes
        $getBaseQuery = function () use ($request) {
            return Clip::with(['questions.options'])
                ->withCount('likes')
                ->withExists(['likes' => function ($query) {
                    $query->where('user_id', Auth::id());
                }]);
        };

        $query = $getBaseQuery();

        if ($request->has('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        $watchedIds = UserProgress::where('user_id', Auth::id())
            ->where('watched', true)
            ->pluck('clip_id');

        // Intentar buscar clips no vistos
        $clip = $query->whereNotIn('id', $watchedIds)
            ->inRandomOrder()
            ->first();

        // Si no hay clips nuevos, buscar cualquiera de la categoría (Respaldo)
        if (!$clip) {
            $query = $getBaseQuery();

            if ($request->has('category') && $request->category !== 'all') {
                $query->where('category', $request->category);
            }

            $clip = $query->inRandomOrder()->first();
        }

        if (!$clip) {
            return response()->json(['message' => 'No more clips'], 204);
        }

        if ($clip) {
            $this->attachUserProgress($clip);
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
                'correct' => false, // O true, da igual, no suma
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

        // En modo práctica no necesitamos chequear progreso DB porque
        // el frontend fuerza el modo "Repaso" con la prop isPracticeMode.

        return Inertia::render('Player', [
            'initialClip' => $clip,
            'userScore' => Auth::user()->score,
            'isPracticeMode' => true,
        ]);
    }

    /**
     * Helper Privado: Adjunta el estado (completado/ganado) al objeto clip
     */
    private function attachUserProgress($clip)
    {
        if (!$clip) return;

        $progress = UserProgress::where('user_id', Auth::id())
            ->where('clip_id', $clip->id)
            ->first();

        // Creamos propiedades dinámicas que Vue leerá
        $clip->completed = $progress ? true : false;

        // Si existe progreso, miramos si acertó. Si no, false.
        $clip->won = $progress ? (bool)$progress->answered_correctly : false;
    }

    /**
     * Funcion para que el usuario pueda dar like a un video
     */
    public function toggleLike($clipId)
    {
        try {
            $userId = auth()->id();

            // 1. Buscamos si ya existe
            $like = Like::where('user_id', $userId)
                ->where('clip_id', $clipId)
                ->first();

            if ($like) {
                $like->delete();
                return response()->json(['status' => 'unliked']);
            }

            // 2. Si no existe, lo creamos
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

        $prompt = "Actúa como un profesor de inglés. Traduce la palabra '{$request->word}' al español, basándote estrictamente en este contexto: '{$request->context}'. 
        Responde ÚNICAMENTE con un JSON válido con esta estructura:
        {
            \"translation\": \"Traducción aquí\",
            \"phonetic\": \"/pronunciación/\"
        }";

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            // Si Google responde con éxito (HTTP 200)
            if ($response->successful()) {
                $aiText = $response->json('candidates.0.content.parts.0.text');
                $aiText = preg_replace('/```json|```/', '', $aiText);
                return response()->json(json_decode(trim($aiText), true));
            } else {
                // SI FALLA, QUE NOS DIGA LA VERDAD DE GOOGLE:
                return response()->json([
                    'translation' => 'Error de Google',
                    'phonetic' => '',
                    'debug_google' => $response->json() // <-- Esto nos mostrará el problema real
                ], 500);
            }
            
        } catch (\Exception $e) {
            // SI FALLA LARAVEL O LA RED:
            return response()->json([
                'translation' => 'Error de Laravel',
                'phonetic' => '',
                'debug_laravel' => $e->getMessage()
            ], 500);
        }
    }
}
