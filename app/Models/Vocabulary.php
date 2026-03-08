<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vocabulary extends Model
{
    // 🌟 Añade esta línea con el nombre REAL de tu tabla en MySQL
    protected $table = 'vocabulary'; 

    protected $fillable = ['term', 'translation', 'phonetic'];
}