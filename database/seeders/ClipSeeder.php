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
        [
            {"start": 0.0, "end": 12.76, "text": "In this way, Slur. I am the one who runs for both the living and the dead. A man who"}, 
            {"start": 12.76, "end": 15.76, "text": "used to a single instinct."}, 
            {"start": 18.76, "end": 19.76, "text": "Survive."}, 
            {"start": 23.76, "end": 26.76, "text": "It is by my hand."}, 
            {"start": 28.76, "end": 38.76, "text": "You arise from the ashes of this world."}, 
            {"start": 42.76, "end": 45.76, "text": "We are not things. We are not things."}, 
            {"start": 45.76, "end": 48.76, "text": "Where is he taking them?"}, 
            {"start": 60.76, "end": 63.76, "text": "I want them back. They're my property."}, 
            {"start": 68.76, "end": 70.76, "text": "I want a day. What a lovely day."}, 
            {"start": 73.76, "end": 75.76, "text": "Want to get through this?"}, 
            {"start": 75.76, "end": 76.76, "text": "Let's go!"}, 
            {"start": 76.76, "end": 77.76, "text": "Go!"}, 
            {"start": 98.76, "end": 104.76, "text": "As the world fell, each of us in our own way was broken."}, 
            {"start": 105.76, "end": 108.76, "text": "It was hard to know who was more crazy."}, 
            {"start": 110.76, "end": 114.76, "text": "Me or everyone else."}
        ]
        JSON;
        

        // 2. Crear el Clip
        $clip = Clip::create([
            'title' => 'Video de Prueba 01',
            'video_url' => '/storage/videos/test_clip.mp4', // Ruta accesible desde web
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
