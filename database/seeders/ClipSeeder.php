<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Clip;
use App\Models\Question;
use App\Models\Option;

class ClipSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Aquí pegas el JSON que copiaste de la terminal
        // Para el ejemplo, pondré uno falso, pero tú pega el tuyo REAL.
        $jsonTranscript = <<<'JSON'
        [{"start": 0.0, "end": 10.24, "text": "Since time began, man has gazed at the stars and he has wondered,"}, 
        {"start": 10.24, "end": 14.64, "text": "am I alone?"}, 
        {"start": 14.64, "end": 23.28, "text": "So much talk of AI in big tech today."}, 
        {"start": 23.28, "end": 27.76, "text": "Virtual worlds will look like when we get there."}, 
        {"start": 27.76, "end": 35.76, "text": "Well folks, we're not going there."}, 
        {"start": 35.76, "end": 39.92, "text": "They are coming here."}, 
        {"start": 39.92, "end": 46.68, "text": "I would like you to meet Eris, the ultimate soldier."}, 
        {"start": 46.68, "end": 55.76, "text": "He's biblically strong, lightning fast, supremely intelligent, and if he is struck down on the"}, 
        {"start": 55.76, "end": 62.76, "text": "battlefield, I will simply make you another."}, 
        {"start": 62.76, "end": 69.76, "text": "You think you're in control of this?"}, 
        {"start": 69.76, "end": 76.76, "text": "You're not."}, 
        {"start": 76.76, "end": 83.76, "text": "What are you?"}, 
        {"start": 83.76, "end": 91.76, "text": "I'm looking for something."}, 
        {"start": 91.76, "end": 98.76, "text": "Something I do not understand."}, 
        {"start": 98.76, "end": 105.76, "text": "Hang on!"}, 
        {"start": 105.76, "end": 112.76, "text": "The malfunctioning program wants to live."},
        {"start": 112.76, "end": 114.76, "text": "Why is that?"}, 
        {"start": 114.76, "end": 117.76, "text": "It's just a feeling."}, 
        {"start": 117.76, "end": 124.76, "text": "Fascinating."}]
        JSON;
        

        // 2. Crear el Clip
        $clip = Clip::create([
            'title' => 'Tron: Ares | Official Trailer',
            'video_url' => '/storage/videos/tron_ares.mp4', // Ruta accesible desde web
            'thumbnail_url' => null, // Opcional por ahora
            'difficulty' => 'A2',
            'transcript_json' => json_decode($jsonTranscript, true) // Decodificamos el string a array PHP
        ]);

        // 3. Crear una Pregunta para este clip
        $question = Question::create([
            'clip_id' => $clip->id,
            'statement' => '¿Qué sistema estamos probando según el video?',
            'points' => 20
        ]);

        // 4. Crear las Opciones
        Option::insert([
            [
                'question_id' => $question->id,
                'text' => 'El sistema de audio',
                'is_correct' => false,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'question_id' => $question->id,
                'text' => 'El sistema de subtítulos',
                'is_correct' => true,
                'created_at' => now(), 'updated_at' => now()
            ],
            [
                'question_id' => $question->id,
                'text' => 'La base de datos',
                'is_correct' => false,
                'created_at' => now(), 'updated_at' => now()
            ]
        ]);
        
        $this->command->info('¡Clip de prueba insertado con éxito!');
    }
}
