<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = ['clip_id', 'statement', 'points'];

    public function clip(): BelongsTo
    {
        return $this->belongsTo(Clip::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }
}