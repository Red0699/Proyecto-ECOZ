<?php

// database/migrations/2025_10_10_000100_add_estacion_id_to_users_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('users', function (Blueprint $t) {
      if (!Schema::hasColumn('users','estacion_id')) {
        $t->foreignId('estacion_id')->nullable()
          ->constrained('estaciones')->nullOnDelete();
      }
    });
  }
  public function down(): void {
    Schema::table('users', function (Blueprint $t) {
      if (Schema::hasColumn('users','estacion_id')) {
        $t->dropConstrainedForeignId('estacion_id');
      }
    });
  }
};
