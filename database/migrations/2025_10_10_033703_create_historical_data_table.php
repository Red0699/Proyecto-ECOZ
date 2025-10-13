<?php
// database/migrations/2025_10_12_000100_create_datos_historicos_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('estaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique(); 
            $table->timestamps();
        });

        Schema::create('datos_historicos', function (Blueprint $table) {
            $table->id();

            // Relación con estaciones (recomendado: FK en lugar de string)
            $table->foreignId('estacion_id')
                  ->constrained('estaciones')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();

            $table->date('fecha');
            $table->time('hora')->nullable();

            // Métricas (decimals para precisión)
            $table->decimal('temperatura_ambiente_c', 8, 2)->nullable();
            $table->decimal('velocidad_viento', 8, 2)->nullable();
            $table->decimal('humedad_ambiente', 8, 4)->nullable(); // fracción (0–1)
            $table->decimal('temperatura_interna_c', 8, 2)->nullable();
            $table->decimal('temperatura_interna_k', 8, 2)->nullable();

            $table->decimal('volumen_gl', 10, 2)->nullable();
            $table->decimal('descargue_combustible_gl', 10, 2)->nullable();
            $table->decimal('ventas_diarias_gl', 10, 2)->nullable();

            $table->decimal('diametro_tanque_in', 8, 2)->nullable();

            $table->decimal('presion_hidrostatica_pa', 12, 4)->nullable();
            $table->decimal('presion_hidrostatica_kpa', 12, 4)->nullable();
            $table->decimal('presion_psi', 8, 4)->nullable();

            // Emisiones/variaciones
            $table->decimal('perdidas_respiracion_kg', 12, 6)->nullable();
            $table->decimal('variacion_formula_gl_1', 8, 4)->nullable();
            $table->decimal('perdidas_operacion_kg', 12, 6)->nullable();
            $table->decimal('variacion_formula_gl_2', 8, 4)->nullable();
            $table->decimal('perdidas_totales_cov_kg', 12, 6)->nullable();
            $table->decimal('cov_a_co2_kg', 12, 6)->nullable();

            $table->decimal('sumatoria_variacion_gl', 10, 4)->nullable();
            $table->decimal('variacion_eds_gl', 10, 2)->nullable();

            // Presiones de saturación
            $table->decimal('presion_sat_octano_mmhg', 8, 4)->nullable();
            $table->decimal('presion_sat_heptano_mmhg', 8, 4)->nullable();
            $table->decimal('presion_sat_tolueno_mmhg', 8, 4)->nullable();

            $table->timestamps();

            // Índice único para evitar duplicados por estación+fecha+hora
            $table->unique(['estacion_id', 'fecha', 'hora'], 'uniq_estacion_fecha_hora');

            // Índices útiles para consultas frecuentes
            $table->index(['fecha']);
            $table->index(['estacion_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('datos_historicos');
    }
};
