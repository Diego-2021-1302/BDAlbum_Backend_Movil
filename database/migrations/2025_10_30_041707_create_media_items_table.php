<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::create('media_items', function (Blueprint $table) {
        $table->id();

        // ruta relativa tipo "storage/media/abc123.jpg"
        $table->string('file_path');

        // 'image' o 'video'
        $table->enum('type', ['image', 'video']);

        // fecha elegida por el usuario
        $table->date('taken_at');

        // descripción opcional
        $table->text('description')->nullable();

        // etiqueta B / D / BD para filtros
        $table->enum('tag', ['B', 'D', 'BD']);

        $table->timestamps();
    });
}

};
