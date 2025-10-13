<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Estacion;

class EstacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['Cota', 'Silvania', 'Ubaté'] as $nombre) {
            Estacion::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
