<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['clip_id', 'statement', 'points'];

    public function clip()
    {
        return $this->belongsTo(Clip::class);
    }

    public function options()
    {
        return $this->hasMany(Option::class);
    }
}
