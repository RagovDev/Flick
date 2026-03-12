<?php

namespace App\Http\Controllers;

use App\Models\Clip;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB; // 🌟 Importamos DB para las transacciones
use Symfony\Component\Process\Process;

class AdminClipController extends Controller
{
    /**
     * Mostrar videos guardados
     */
    public function index()
    {
        // Traemos todos los videos ordenados del más nuevo al más viejo
        $clips = \App\Models\Clip::orderBy('created_at', 'desc')->get();

        return inertia('Admin/ManageClips', [
            'clips' => $clips
        ]);
    }

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

        // 2. Subir el video temporalmente
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
                'debug' => env('APP_DEBUG') ? $process->getErrorOutput() : 'Error interno de servidor.'
            ]);
        }

        // 4. Analizar la respuesta de Python
        $pythonOutput = $process->getOutput();
        $transcriptArray = json_decode($pythonOutput, true);

        // 🌟 Verificamos también que json_last_error sea limpio
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($transcriptArray) || isset($transcriptArray['error'])) {
            Storage::disk('public')->delete($path);
            return back()->withErrors([
                'error' => 'Error de Whisper/Decodificación',
                'debug' => $transcriptArray['error'] ?? 'El script de Python no devolvió un JSON válido.'
            ]);
        }

        // 5. Unir los subtítulos para la IA
        $fullText = collect($transcriptArray)->pluck('text')->join(' ');

        if (empty(trim($fullText))) {
            Storage::disk('public')->delete($path);
            return back()->withErrors(['error' => 'Error: No se encontró audio legible o el video es silencioso.']);
        }

        // 6. Generar Quiz con Gemini
        $quizData = $this->generateQuizWithAI($fullText);

        if (!$quizData) {
            Storage::disk('public')->delete($path);
            return back()->withErrors(['error' => 'Error: La IA falló al generar el quiz o la API Key no está configurada. Intenta de nuevo.']);
        }

        // 7. GUARDAR EN BASE DE DATOS (🌟 Ahora protegido por Transacción)
        try {
            DB::transaction(function () use ($request, $path, $transcriptArray, $quizData) {
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
            });

            return redirect()->route('admin.dashboard')->with('success', '¡Clip procesado, analizado y publicado con éxito!');
        } catch (\Exception $e) {
            // Si la base de datos falla, borramos el video para no llenar el disco de basura
            Storage::disk('public')->delete($path);
            return back()->withErrors(['error' => 'Ocurrió un problema crítico al guardar en la base de datos.']);
        }
    }

    /**
     * Helper: Genera el Quiz usando Gemini AI
     */
    private function generateQuizWithAI($text)
    {
        $apiKey = env('GEMINI_API_KEY');

        if (empty($apiKey)) {
            return null; // 🌟 Corrección: Devolvemos null para que el store maneje el error
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
            \"difficulty\": \"...\"
        }";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite-preview:generateContent?key=" . $apiKey, [
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

    /**
     * Borrar video
     */
    public function destroy($id)
    {
        // 1. Buscamos el video en la base de datos
        $clip = Clip::findOrFail($id);

        // 2. Eliminamos el archivo físico del disco
        // Al usar disk('public'), Laravel apunta automáticamente a storage/app/public/
        if ($clip->video_path && Storage::disk('public')->exists($clip->video_path)) {
            Storage::disk('public')->delete($clip->video_path);
        }

        // 3. (Opcional) Si también guardas la miniatura en tu servidor, bórrala aquí:
        // if ($clip->thumbnail_path && Storage::disk('public')->exists($clip->thumbnail_path)) {
        //     Storage::disk('public')->delete($clip->thumbnail_path);
        // }

        // 4. Eliminamos el registro de la base de datos
        // Si tienes "onDelete('cascade')" en tus migraciones, esto borrará también 
        // los subtítulos y preguntas asociadas automáticamente.
        $clip->delete();

        // 5. Regresamos a la vista anterior (el panel o la lista)
        // Inertia recargará los datos sin recargar la página completa
        return redirect()->back();
    }

    /**
     * Actualizar video
     */
    public function update(Request $request, $id)
{
    // 1. Validamos que los datos sean correctos
    $request->validate([
        'title' => 'required|string|max:255',
        'category' => 'required|string|in:movies,music,tech,travel',
    ]);

    // 2. Buscamos el video
    $clip = Clip::findOrFail($id);

    // 3. Actualizamos solo el título y la categoría
    $clip->update([
        'title' => $request->title,
        'category' => $request->category,
    ]);

    // 4. Regresamos con un mensaje de éxito
    return back()->with('success', 'Video actualizado correctamente.');
}
}
