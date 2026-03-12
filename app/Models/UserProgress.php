<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProgress extends Model
{
    protected $table = 'user_progress'; 

    public $timestamps = false; 

    protected $fillable = ['user_id', 'clip_id', 'watched', 'answered_correctly', 'viewed_at'];

    // 🌟 Magia Pura: Convierte los 1/0 en booleanos y el texto de la fecha en un objeto Carbon
    protected function casts(): array
    {
        return [
            'watched' => 'boolean',
            'answered_correctly' => 'boolean',
            'viewed_at' => 'datetime',
        ];
    }

    public function clip(): BelongsTo
    {
        return $this->belongsTo(Clip::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}