<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_xxxxxx_create_historical_data_table.php
    public function up(): void
    {
        Schema::create('historical_data', function (Blueprint $table) {
            $table->id();
            $table->string('station');
            $table->date('record_date');
            $table->time('record_time')->nullable();

            // Usamos decimal para mayor precisión en cálculos financieros o científicos
            $table->decimal('ambient_temp_c', 8, 2)->nullable();
            $table->decimal('wind_speed', 8, 2)->nullable();
            $table->decimal('ambient_humidity', 8, 4)->nullable(); // 4 decimales para porcentajes
            $table->decimal('internal_temp_c', 8, 2)->nullable();
            $table->decimal('internal_temp_k', 8, 2)->nullable();
            $table->decimal('volume_gl', 10, 2)->nullable();
            $table->decimal('fuel_discharge_gl', 10, 2)->nullable();
            $table->decimal('tank_diameter_in', 8, 2)->nullable();
            $table->decimal('hydrostatic_pressure_pa', 12, 4)->nullable();
            $table->decimal('hydrostatic_pressure_kpa', 12, 4)->nullable();
            $table->decimal('pressure_psi', 8, 4)->nullable();
            $table->decimal('octane_sat_pressure_mmhg', 8, 4)->nullable();
            $table->decimal('heptane_sat_pressure_mmhg', 8, 4)->nullable();
            $table->decimal('toluene_sat_pressure_mmhg', 8, 4)->nullable();
            $table->decimal('daily_sales_gl', 10, 2)->nullable();
            $table->decimal('voc_breathing_emissions_kg', 12, 6)->nullable();
            $table->decimal('formula_variance_gl_1', 8, 4)->nullable();
            $table->decimal('voc_working_emissions_kg', 12, 6)->nullable();
            $table->decimal('formula_variance_gl_2', 8, 4)->nullable();
            $table->decimal('total_voc_emissions_kg', 12, 6)->nullable();
            $table->decimal('voc_to_co2_kg', 12, 6)->nullable();
            $table->decimal('total_variance_gl', 8, 4)->nullable();
            $table->decimal('station_variance_gl', 8, 2)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historical_data');
    }
};
