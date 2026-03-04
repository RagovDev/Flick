<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    use HasFactory;

    // Permitir asignación masiva para estas columnas
    protected $fillable = [
        'user_id',
        'clip_id',
    ];

    // Relación: Un Like pertenece a un Usuario
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación: Un Like pertenece a un Clip
    public function clip()
    {
        return $this->belongsTo(Clip::class);
    }
}