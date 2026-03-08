<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;
use App\Models\Vocabulary;

class VocabularyController extends Controller
{
    /**
     * Guarda una palabra y busca su traducción automáticamente.
     */
    public function store(Request $request)
    {
        $request->validate([
            'term' => 'required|string|max:255',
        ]);

        $term = strtolower(trim($request->term));

        // 1. Buscamos si la palabra ya existe en el diccionario global
        $word = Word::where('term', $term)->first();

        // 2. Si NO existe, la creamos Y buscamos su traducción
        if (!$word) {
            $translation = 'Traducción no encontrada'; // Valor por defecto

            try {
                // LLAMADA A LA API (MyMemory: Inglés -> Español)
                $response = Http::get('https://api.mymemory.translated.net/get', [
                    'q' => $term,
                    'langpair' => 'en|es'
                ]);

                if ($response->successful()) {
                    // Extraemos la traducción del JSON
                    $translation = $response->json()['responseData']['translatedText'] ?? $translation;
                }
            } catch (\Exception $e) {
                // Si falla internet, no pasa nada, guardamos sin traducción
            }

            // Guardamos en la BD Global
            $word = Word::create([
                'term' => $term,
                'translation' => $translation,
            ]);
        }

        // 3. Asignamos la palabra al usuario (Si no la tiene ya)
        $user = Auth::user();

        if (!$user->words()->where('word_id', $word->id)->exists()) {
            $user->words()->attach($word->id, [
                'mastery_level' => 0,
                'next_review_at' => now(),
            ]);

            return response()->json([
                'status' => 'saved',
                'message' => 'Guardado: ' . $word->translation, // Devolvemos la traducción para feedback
                'word' => $word
            ]);
        }

        return response()->json([
            'status' => 'exists',
            'message' => 'Ya la tienes: ' . $word->translation,
            'word' => $word
        ]);
    }

    /**
     * Muestra la colección del usuario.
     */
    public function index()
    {
        $words = Auth::user()->words()
            ->orderByPivot('created_at', 'desc')
            ->get()
            ->map(function ($word) {
                return [
                    'id' => $word->id,
                    'term' => $word->term,
                    'translation' => $word->translation, // Ahora esto tendrá valor real
                    'level' => $word->pivot->mastery_level,
                    'review_count' => $word->pivot->review_count,
                    'added_at' => $word->pivot->created_at->diffForHumans(),
                ];
            });

        return Inertia::render('Vocabulary/Index', [
            'words' => $words
        ]);
    }

    /**
     * Modo Práctica: Devuelve palabras aleatorias para repasar.
     */
    public function practice()
    {
        // Obtenemos hasta 10 palabras aleatorias del usuario
        // Idealmente aquí filtraríamos por 'next_review_at', pero para el MVP usamos random
        $words = Auth::user()->words()
            ->inRandomOrder()
            ->limit(10)
            ->get()
            ->map(function ($word) {
                return [
                    'id' => $word->id,
                    'term' => $word->term,
                    'translation' => $word->translation,
                    'level' => $word->pivot->mastery_level,
                ];
            });

        return Inertia::render('Vocabulary/Practice', [
            'words' => $words
        ]);
    }

    /**
     * Elimina una palabra de tu coleccion.
     */
    public function destroy(Vocabulary $vocabulary)
    {
        // Verificamos que la palabra pertenezca al usuario que intenta borrarla
        if ($vocabulary->user_id !== Auth::id()) {
            abort(403);
        }

        $vocabulary->delete();

        // Redirigimos de vuelta para que Inertia refresque la lista automáticamente
        return redirect()->back();
    }
}
