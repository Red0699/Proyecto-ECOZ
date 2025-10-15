<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Asegúrate de tener la tabla 'data_imports' creada antes de correr esto.
        //    (si ya la tienes, no debes tocar nada aquí)

        Schema::table('datos_historicos', function (Blueprint $table) {
            // 2) Añadir columna 'lote_id' si no existe, y crear la FK con cascadeOnDelete
            if (!Schema::hasColumn('datos_historicos', 'lote_id')) {
                $table->foreignId('lote_id')
                      ->nullable()                         // ← importante para no romper datos existentes
                      ->after('estacion_id')
                      ->constrained('data_imports')        // referencias data_imports.id
                      ->cascadeOnDelete();                 // borrar lote ⇒ borra datos del lote
            } else {
                // Si la columna existe pero sin FK, la creamos
                // (Algunas instalaciones requieren dropear primero una FK previa, si existiera)
                try {
                    $table->dropForeign(['lote_id']);
                } catch (\Throwable $e) {
                    // ignorar si no existía
                }
                $table->foreign('lote_id')
                      ->references('id')
                      ->on('data_imports')
                      ->cascadeOnDelete();
            }

            // 3) Opcional: índice para consultas por lote
            $table->index('lote_id', 'datos_historicos_lote_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('datos_historicos', function (Blueprint $table) {
            // quitar índice y FK en orden correcto
            if (Schema::hasColumn('datos_historicos', 'lote_id')) {
                try {
                    $table->dropIndex('datos_historicos_lote_id_index');
                } catch (\Throwable $e) {
                    // ignorar si no existía
                }
                try {
                    $table->dropForeign(['lote_id']);
                } catch (\Throwable $e) {
                    // ignorar si no existía
                }
                $table->dropColumn('lote_id');
            }
        });
    }
};
