<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('estacion_id')
                  ->nullable()
                  ->constrained('estaciones')
                  ->nullOnDelete()
                  ->after('role_id'); // o donde prefieras
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('estacion_id');
        });
    }
};
