<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo; // <--- Importante

class UserProgress extends Model
{
    protected $table = 'user_progress'; 

    public $timestamps = false; 

    protected $fillable = ['user_id', 'clip_id', 'watched', 'answered_correctly', 'viewed_at'];

    /**
     * Relación: Un registro de progreso pertenece a un Video (Clip).
     */
    public function clip(): BelongsTo
    {
        return $this->belongsTo(Clip::class);
    }

    /**
     * (Opcional) Relación: Un registro de progreso pertenece a un Usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
