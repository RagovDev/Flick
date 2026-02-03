<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clip extends Model
{
    // Permitir asignación masiva para estos campos
    protected $fillable = ['title', 'video_url', 'difficulty', 'category', 'transcript_json'];

    // En tu modelo Clip.php
    protected $appends = ['thumbnail_url'];

    // Importante: castear el JSON a array automáticamente
    protected $casts = [
        'transcript_json' => 'array',
    ];

    public function getThumbnailUrlAttribute()
    {
        // OPCIÓN PRO: Usar una imagen de "Cine/Estudio" de alta calidad como defecto
        // Esto se ve mucho mejor que un cuadro gris.
        return "https://images.unsplash.com/photo-1485846234645-a62644f84728?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80";
        
        // NOTA: Si en el futuro subes imágenes manuales a /storage/thumbnails/, 
        // podrías cambiar esto por:
        // return "/storage/thumbnails/{$this->id}.jpg";
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }
    
    public function userProgress()
    {
        return $this->hasMany(UserProgress::class);
    }
}
