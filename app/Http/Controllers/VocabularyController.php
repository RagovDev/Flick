<?php

namespace App\Http\Controllers;

use App\Models\Word;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class VocabularyController extends Controller
{
    /**
     * Guarda la palabra usando los datos que la IA ya generó en el frontend.
     */
    public function store(Request $request)
    {
        $request->validate([
            'term' => 'required|string|max:255',
            'translation' => 'required|string|max:255',
            'phonetic' => 'nullable|string|max:255',
        ]);

        $term = strtolower(trim($request->term));

        // 🌟 1. Mantenemos firstOrCreate: Si la palabra ya existe globalmente, no la sobrescribe
        $word = Word::firstOrCreate(
            ['term' => $term],
            [
                'translation' => $request->translation,
                'phonetic' => $request->phonetic ?? '',
            ]
        );

        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 🌟 2. syncWithoutDetaching vincula la palabra al usuario solo si no la tiene ya
        $user->words()->syncWithoutDetaching([
            $word->id => [
                'mastery_level' => 0,
                'next_review_at' => now()->addDays(1),
            ]
        ]);

        return redirect()->back();
    }

    /**
     * Muestra la colección del usuario con la fonética.
     */
    public function index()
    {
        /** @var \App\Models\User $user */
        $words = Auth::user()->words()
            ->orderByPivot('created_at', 'desc')
            ->get()
            ->map(function ($word) {
                return [
                    'id' => $word->id,
                    'term' => $word->term,
                    'translation' => $word->translation,
                    'phonetic' => $word->phonetic, 
                    'level' => $word->pivot->mastery_level,
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
        $words = Auth::user()->words()
            ->inRandomOrder()
            ->limit(10)
            ->get()
            ->map(function ($word) {
                return [
                    'id' => $word->id,
                    'term' => $word->term,
                    'translation' => $word->translation,
                    'phonetic' => $word->phonetic,
                    'level' => $word->pivot->mastery_level,
                ];
            });

        return Inertia::render('Vocabulary/Practice', [
            'words' => $words
        ]);
    }

    /**
     * Elimina una palabra de la colección del usuario.
     */
    public function destroy($id)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 🌟 3. detach() elimina la relación de la tabla intermedia de forma directa y segura
        $user->words()->detach($id);

        return redirect()->back();
    }
}