<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Diccionario Global
        Schema::create('words', function (Blueprint $table) {
            $table->id();
            $table->string('term')->unique(); // La palabra en inglés (ej: 'apple')
            $table->string('translation')->nullable(); // Traducción en español
            $table->string('phonetic')->nullable(); // Pronunciación (ej: '/ˈapəl/')
            $table->timestamps();
        });

        // 2. Progreso del Usuario (Tabla Pivote)
        Schema::create('word_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('word_id')->constrained()->cascadeOnDelete();
            
            // Datos de Gamificación / Aprendizaje
            $table->integer('mastery_level')->default(0); // 0=Nueva, 1=Aprendiendo, 5=Dominada
            $table->timestamp('next_review_at')->nullable(); // Para sistema de repaso espaciado (Flashcards)
            $table->integer('review_count')->default(0); // Cuántas veces la ha repasado
            
            $table->timestamps();
            
            // Evitar duplicados (un usuario no puede tener la misma palabra 2 veces)
            $table->unique(['user_id', 'word_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('words');
    }
};
