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
        Schema::create('clips', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Ej: "Pulp Fiction - Burger Scene"
            $table->string('video_url'); // Ruta local o S3
            $table->string('thumbnail_url')->nullable(); // Poster del video
            $table->enum('difficulty', ['A1', 'A2', 'B1', 'B2', 'C1'])->default('B1');
            $table->json('transcript_json')->nullable(); // Aquí vivirá la magia de los subtítulos
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clips');
    }
};
