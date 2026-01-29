<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Word extends Model
{
    use HasFactory;

    protected $fillable = [
        'term',
        'translation',
        'phonetic',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['mastery_level', 'next_review_at']);
    }
}