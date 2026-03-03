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

        // 2. Subir el video al almacenamiento
        $path = $request->file('video_file')->store('videos', 'public');
        $fullVideoPath = storage_path('app/public/' . $path);
        $scriptPath = base_path('scripts/generate_subs.py');

        // 3. Ejecutar Python (Whisper)
        $process = new Process(['python', $scriptPath, $fullVideoPath]);
        $process->setTimeout(300); // 5 minutos límite
        $process->run();

        // Trampa: Si el comando de Python falla a nivel de sistema
        if (!$process->isSuccessful()) {
            Storage::disk('public')->delete($path); // Limpiamos la basura
            dd('🚨 ERROR FATAL AL EJECUTAR PYTHON:', $process->getErrorOutput());
        }

        // 4. Analizar la respuesta de Python
        $pythonOutput = $process->getOutput();
        $transcriptArray = json_decode($pythonOutput, true);

        // Trampa: Si el script de Python devolvió un mensaje de error manual
        if (isset($transcriptArray['error'])) {
            Storage::disk('public')->delete($path);
            dd('🚨 ERROR DE WHISPER (PYTHON):', $transcriptArray['error']);
        }

        // Trampa: Si el JSON no se pudo decodificar por algún motivo
        if (!is_array($transcriptArray)) {
            Storage::disk('public')->delete($path);
            dd('🚨 ERROR DE DECODIFICACIÓN:', 'El output de Python no es un JSON válido.', $pythonOutput);
        }

        // 5. Unir los subtítulos para pasárselos a la IA
        $fullText = collect($transcriptArray)->pluck('text')->join(' ');

        // Trampa: Si Whisper no encontró diálogo en el video
        if (empty(trim($fullText))) {
            Storage::disk('public')->delete($path);
            dd('🚨 ERROR: Python no extrajo ningún texto del video. ¿El video tiene audio legible?');
        }

        // 6. Hablar con Gemini API
        $quizData = $this->generateQuizWithAI($fullText);

        // Trampa: Si Gemini falló y devolvió null (el error específico se mostrará dentro de la función)
        if (!$quizData) {
            Storage::disk('public')->delete($path);
            dd('🚨 ERROR FINAL: La IA falló y devolvió null.');
        }

        // 7. GUARDAR EN BASE DE DATOS
        $clip = Clip::create([
            'title' => $request->title,
            'category' => $request->category,
            'video_url' => '/storage/' . $path,
            'transcript_json' => $transcriptArray, // Subtítulos de Whisper
            'difficulty' => 'A1',
        ]);

        $question = Question::create([
            'clip_id' => $clip->id,
            'statement' => $quizData['question'],
            'points' => 20,
        ]);

        Option::create(['question_id' => $question->id, 'text' => $quizData['correct_option'], 'is_correct' => true]);
        Option::create(['question_id' => $question->id, 'text' => $quizData['wrong_option_1'], 'is_correct' => false]);
        Option::create(['question_id' => $question->id, 'text' => $quizData['wrong_option_2'], 'is_correct' => false]);

        // ¡Éxito! Volvemos al dashboard
        return redirect()->route('dashboard')->with('success', '¡Video procesado por IA y publicado con éxito!');
    }

    /**
     * Función privada que habla con la API de Gemini
     */
    private function generateQuizWithAI($text)
    {
        $apiKey = env('GEMINI_API_KEY');

        // Trampa: Verificar si la API Key existe
        if (empty($apiKey)) {
            dd('🚨 ERROR FATAL: No encontré la variable GEMINI_API_KEY en tu archivo .env. ¡Agrégala!');
        }

        // El "Prompt" maestro mejorado y estricto
        $prompt = "Lee este diálogo corto extraído de un video: '{$text}'. 
        Crea una pregunta de opción múltiple para evaluar la comprensión de la escena o el vocabulario en inglés.
        
        REGLAS ESTRICTAS:
        1. La pregunta ('question') DEBE ser directa y al grano.
        2. NO uses introducciones, NO saludes, NO des explicaciones.
        3. Longitud máxima de la pregunta: 15 palabras. (Ejemplo: '¿Qué significa la frase X?', '¿Por qué el personaje hace Y?').
        4. Las opciones de respuesta deben ser muy cortas (1 a 5 palabras).
        5. DEBES responder ÚNICAMENTE con un objeto JSON válido con la siguiente estructura exacta, sin texto adicional ni formato markdown:
        {
            \"question\": \"Tu pregunta directa aquí\",
            \"correct_option\": \"Respuesta correcta\",
            \"wrong_option_1\": \"Falsa 1\",
            \"wrong_option_2\": \"Falsa 2\"
        }";

        try {
            // Petición HTTP a Google (withoutVerifying para evitar problemas de certificados locales)
            $response = Http::withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ]
            ]);

            if ($response->successful()) {
                $aiText = $response->json('candidates.0.content.parts.0.text');

                // Limpiar posibles etiquetas markdown de la IA
                $aiText = preg_replace('/```json|```/', '', $aiText);

                $decodedData = json_decode(trim($aiText), true);

                // Trampa: Si la IA no devolvió el JSON correcto
                if (!$decodedData || !isset($decodedData['question'])) {
                    dd('🚨 ERROR DE FORMATO IA: La IA no devolvió un JSON válido.', 'Texto Original:', $aiText);
                }

                return $decodedData;
            } else {
                // Trampa: Si Google rechazó la petición
                dd('🚨 ERROR DE API GOOGLE:', $response->status(), $response->body());
            }
        } catch (\Exception $e) {
            // Trampa: Error de conexión del servidor
            dd('🚨 ERROR DE CONEXIÓN (LARAVEL HTTP):', $e->getMessage());
        }

        return null;
    }
}
