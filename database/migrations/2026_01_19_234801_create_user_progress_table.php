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
        Schema::create('user_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('clip_id')->constrained()->onDelete('cascade');
            $table->boolean('watched')->default(false);
            $table->boolean('answered_correctly')->nullable(); // Null = no respondió aún
            $table->timestamp('viewed_at')->useCurrent();
            
            // Evitar duplicados: un usuario solo tiene un registro de progreso por video
            $table->unique(['user_id', 'clip_id']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_progress');
    }
};
