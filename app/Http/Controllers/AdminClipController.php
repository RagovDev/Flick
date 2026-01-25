<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Clip;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class AdminClipController extends Controller
{
    // 1. Mostrar el formulario de subida
    public function create()
    {
        return Inertia::render('Admin/CreateClip');
    }

    // 2. Guardar el video y sus datos
    public function store(Request $request)
    {
        // Validación básica
        $request->validate([
            'title' => 'required|string|max:255',
            'video_file' => 'required|file|mimetypes:video/mp4,video/quicktime|max:50000', // Max 50MB
            'transcript_json' => 'required|json', // El usuario pegará el JSON aquí
            'question_text' => 'required|string',
            'correct_option' => 'required|string',
            'wrong_option_1' => 'required|string',
            'wrong_option_2' => 'required|string',
        ]);

        // A. Subir el video al disco 'public'
        $path = $request->file('video_file')->store('videos', 'public');

        // B. Crear el Clip
        $clip = Clip::create([
            'title' => $request->title,
            'video_url' => '/storage/' . $path,
            'transcript_json' => json_decode($request->transcript_json),
            'difficulty' => 'A1', // Hardcodeado por ahora o agrégalo al form
        ]);

        // C. Crear la Pregunta
        $question = Question::create([
            'clip_id' => $clip->id,
            'statement' => $request->question_text,
            'points' => 20,
        ]);

        // D. Crear las Opciones (1 Correcta, 2 Incorrectas)
        // Opción Correcta
        Option::create([
            'question_id' => $question->id,
            'text' => $request->correct_option,
            'is_correct' => true,
        ]);
        // Incorrecta 1
        Option::create([
            'question_id' => $question->id,
            'text' => $request->wrong_option_1,
            'is_correct' => false,
        ]);
        // Incorrecta 2
        Option::create([
            'question_id' => $question->id,
            'text' => $request->wrong_option_2,
            'is_correct' => false,
        ]);

        return redirect()->route('flick.index')->with('success', '¡Video publicado con éxito!');
    }
}
