<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('normativas', function (Blueprint $table) {
            $table->id();
            // Campos mínimos según tu documento Word
            $table->string('codigo', 150)->nullable();      // p.ej., "Decreto 1717 de 2008 - Art. 26"
            $table->string('titulo', 255);                  // título legible
            $table->text('reglamentacion')->nullable();     // qué exige / regula
            $table->text('sanciones')->nullable();          // sanciones (si aplica)

            // Ámbito por estación (NULL = aplica a todas)
            $table->foreignId('estacion_id')->nullable()
                  ->constrained('estaciones')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('normativas');
    }
};
