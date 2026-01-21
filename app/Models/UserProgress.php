<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    protected $table = 'user_progress'; // Forzar nombre si Laravel no lo pluraliza bien

    // IMPORTANTE: Desactivamos los timestamps automáticos (created_at, updated_at)
    // porque nosotros manejamos 'viewed_at' manualmente.
    public $timestamps = false;
    
    protected $fillable = ['user_id', 'clip_id', 'watched', 'answered_correctly', 'viewed_at'];
}
