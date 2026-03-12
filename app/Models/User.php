<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
    ];

    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    // Esto engaña a Vue haciéndole creer que "is_admin" es una columna real
    protected $appends = ['is_admin'];

    public function getIsAdminAttribute()
    {
        return $this->hasRole('admin');
    }

    // Relación: Las palabras que el usuario está aprendiendo
    public function words()
    {
        return $this->belongsToMany(Word::class, 'word_user')
            ->withPivot(['mastery_level', 'next_review_at', 'review_count'])
            ->withTimestamps();
    }
}