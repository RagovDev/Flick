<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Clip;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

class AdminClipController extends Controller
{
    /**
     * Mostrar el formulario de subida
     */
    public function create()
    {
        return Inertia::render('Admin/CreateClip');
    }

    /**
     * Guardar el video, ejecutar Whisper y generar Quiz con Gemini
     */
    public function store(Request $request)
    {
        // 1. Validación
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string',
            'video_file' => 'required|file|mimetypes:video/mp4,video/quicktime|max:50000',
        ]);

        // 2. Subir el video
        $path = $request->file('video_file')->store('videos', 'public');
        $fullVideoPath = storage_path('app/public/' . $path);
        $scriptPath = base_path('scripts/generate_subs.py');

        // 3. Ejecutar Python (Whisper)
        $process = new Process(['python', $scriptPath, $fullVideoPath]);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            Storage::disk('public')->delete($path);
            return back()->withErrors([
                'error' => 'Error al ejecutar python.',
                'debug' => $process->getErrorOutput()
            ]);
        }

        // 4. Analizar la respuesta de Python
        $pythonOutput = $process->getOutput();
        $transcriptArray = json_decode($pythonOutput, true);

        if (!is_array($transcriptArray) || isset($transcriptArray['error'])) {
            Storage::disk('public')->delete($path);
            return back()->withErrors([
                'error' => 'Error de Whisper/Decodificación',
                'debug' => $transcriptArray['error'] ?? 'JSON Inválido'
            ]);
        }

        // 5. Unir los subtítulos para la IA
        $fullText = collect($transcriptArray)->pluck('text')->join(' ');

        if (empty(trim($fullText))) {
            Storage::disk('public')->delete($path);
            return back()->withErrors(['error' => 'Error, no se encontró audio legible.']);
        }

        // 6. Generar Quiz con Gemini
        $quizData = $this->generateQuizWithAI($fullText);

        if (!$quizData) {
            Storage::disk('public')->delete($path);
            return back()->withErrors(['error' => 'Error, la IA falló al generar el quiz. Intenta con otro clip.']);
        }

        // 7. GUARDAR EN BASE DE DATOS
        $clip = Clip::create([
            'title' => $request->title,
            'category' => $request->category,
            'video_url' => '/storage/' . $path,
            'transcript_json' => $transcriptArray,
            'difficulty' => $quizData['difficulty'] ?? 'A1',
        ]);

        $question = Question::create([
            'clip_id' => $clip->id,
            'statement' => $quizData['question'],
            'points' => 20,
        ]);

        Option::create(['question_id' => $question->id, 'text' => $quizData['correct_option'], 'is_correct' => true]);
        Option::create(['question_id' => $question->id, 'text' => $quizData['wrong_option_1'], 'is_correct' => false]);
        Option::create(['question_id' => $question->id, 'text' => $quizData['wrong_option_2'], 'is_correct' => false]);

        return redirect()->route('admin.dashboard')->with('success', '¡Clip normalizado y publicado!');
    }

    private function generateQuizWithAI($text)
    {
        $apiKey = env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            return back()->withErrors(['error' => 'Error. GEMINI_API_KEY no configurada.']);
        }

        $prompt = "Read this dialogue: '{$text}'. 
        Create a short multiple-choice question (max 15 words) about the vocabulary or situation.
        Also, classify the CEFR English level of this dialogue (A1, A2, B1, B2, C1, or C2).
        Respond ONLY with valid JSON:
        {
            \"question\": \"...\",
            \"correct_option\": \"...\",
            \"wrong_option_1\": \"...\",
            \"wrong_option_2\": \"...\",
            \"difficulty\": \"B1\"
        }";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            if ($response->successful()) {
                $aiText = $response->json('candidates.0.content.parts.0.text');
                $aiText = preg_replace('/```json|```/', '', $aiText);
                return json_decode(trim($aiText), true);
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }
}
