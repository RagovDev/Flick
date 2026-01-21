<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clip extends Model
{
    // Permitir asignación masiva para estos campos
    protected $fillable = ['title', 'video_url', 'thumbnail_url', 'difficulty', 'transcript_json'];

    // Importante: castear el JSON a array automáticamente
    protected $casts = [
        'transcript_json' => 'array',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
    
    public function userProgress()
    {
        return $this->hasMany(UserProgress::class);
    }
}
