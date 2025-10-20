<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Estacion;

class EstacionSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['nombre' => 'Cota',     'capacidad_gl' => 12000],
            ['nombre' => 'Silvania', 'capacidad_gl' => 9000],
            ['nombre' => 'Ubaté',    'capacidad_gl' => 12000],
        ];

        foreach ($data as $item) {
            Estacion::updateOrCreate(
                ['nombre' => $item['nombre']],
                ['capacidad_gl' => $item['capacidad_gl']]
            );
        }
    }
}
