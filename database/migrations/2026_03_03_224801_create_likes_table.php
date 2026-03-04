<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('clip_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Un usuario solo puede darle Like una vez a un video
            $table->unique(['user_id', 'clip_id']); 
        });
    }
};
