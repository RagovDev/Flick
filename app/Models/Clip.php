<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clip extends Model
{
    // Permitir asignación masiva para estos campos
    protected $fillable = ['title', 'video_url', 'difficulty', 'category', 'transcript_json'];

    protected $appends = ['thumbnail_url', 'is_liked', 'likes_count'];

    // Importante: castear el JSON a array automáticamente
    protected $casts = [
        'transcript_json' => 'array',
    ];

    public function getThumbnailUrlAttribute()
    {
        // Imagen de defecto premium
        return "https://images.unsplash.com/photo-1485846234645-a62644f84728?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80";
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function userProgress()
    {
        return $this->hasMany(UserProgress::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function getIsLikedAttribute()
    {
        return (bool) ($this->attributes['likes_exists'] ?? false);
    }

    public function getLikesCountAttribute()
    {
        return (int) ($this->attributes['likes_count'] ?? 0);
    }
}