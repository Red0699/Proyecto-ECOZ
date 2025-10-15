<?php

// database/migrations/2025_10_10_000010_create_data_imports_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('data_imports', function (Blueprint $t) {
      $t->id();
      $t->foreignId('estacion_id')->constrained('estaciones')->cascadeOnDelete();
      $t->foreignId('user_id')->constrained('users')->cascadeOnDelete();
      $t->string('archivo');
      $t->string('mime')->nullable();
      $t->unsignedBigInteger('size')->nullable();
      $t->string('path')->nullable();         // opcional: guardar archivo
      $t->unsignedInteger('filas')->default(0);
      $t->enum('estado', ['ok','processing','failed'])->default('ok'); // simple
      $t->timestamps();
    });
  }
  public function down(): void { Schema::dropIfExists('data_imports'); }
};

