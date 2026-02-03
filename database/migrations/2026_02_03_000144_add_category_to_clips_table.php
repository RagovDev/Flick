<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('clips', function (Blueprint $table) {
            // Creamos el campo 'category' y le ponemos 'general' por defecto
            $table->string('category')->default('general')->after('difficulty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('clips', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
