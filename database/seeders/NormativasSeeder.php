<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NormativasSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('normativas')->insert([
            [
                'codigo' => 'Decreto 1717 de 2008 — Art. 26',
                'titulo' => 'Control de inventario para evitar ingreso de aire',
                'reglamentacion' => 'Volumen mayor al 30% de capacidad del tanque o diferencia de 400 gal frente a capacidad mínima definida por la marca (ingreso de aire).',
                'sanciones' => 'Régimen del Decreto 4299 de 2005 (Art. 32): amonestación, multas, suspensión del servicio y cancelación de la autorización, según la gravedad.',
                'estacion_id' => null, // aplica a todas
            ],
            [
                'codigo' => 'Manual Ecopetrol Cap. 19 — 3.2',
                'titulo' => 'Clasificación de pérdidas en tanques (respiración y trabajo)',
                'reglamentacion' => 'Regula pérdidas por evaporación en tanques (almacenamiento y operación), calculadas en lb/año bajo condiciones técnicas específicas.',
                'sanciones' => 'No trae sanciones legales.',
                'estacion_id' => null,
            ],
            [
                'codigo' => 'ISO 14001 — 4.4',
                'titulo' => 'Sistema de Gestión Ambiental (SGA)',
                'reglamentacion' => 'Implementar un SGA que incluya procesos necesarios y sus interacciones, de acuerdo con los requisitos.',
                'sanciones' => 'Si hay infracción ambiental, aplica Ley 1333 de 2009: amonestación escrita, multas hasta 100.000 SMLMV, cierre del establecimiento, revocatoria de licencias.',
                'estacion_id' => null,
            ],
            [
                'codigo' => 'ISO 14064 — 3.2',
                'titulo' => 'Términos para inventarios de GEI',
                'reglamentacion' => 'Estimación del proceso derivada de cálculo fundamentado en mediciones directas.',
                'sanciones' => 'Si hay infracción ambiental, aplica Ley 1333 de 2009: amonestación escrita, multas hasta 100.000 SMLMV, cierre del establecimiento, revocatoria de licencias.',
                'estacion_id' => null,
            ],
            [
                'codigo' => 'Resolución 1447 de 2018 — Art. 6',
                'titulo' => 'Cumplimiento RENARE en operaciones de mayor emisión',
                'reglamentacion' => 'Cumplimiento al RENARE en procedimientos operativos con mayor emisión (p. ej., descargues en horas de alta temperatura).',
                'sanciones' => 'Si hay infracción ambiental, aplica Ley 1333 de 2009: amonestación escrita, multas hasta 100.000 SMLMV, cierre del establecimiento, revocatoria de licencias.',
                'estacion_id' => null,
            ],
            [
                'codigo' => 'Ley 1931 de 2018 — Art. 14',
                'titulo' => 'Gestión del cambio climático con reportes claros e inventarios GEI',
                'reglamentacion' => 'La gestión del cambio climático debe apoyarse en reportes claros y simples con los inventarios nacionales de GEI.',
                'sanciones' => 'Las infracciones ambientales asociadas al incumplimiento de obligaciones reglamentadas se tramitan por Ley 1333 de 2009.',
                'estacion_id' => null,
            ],
            [
                'codigo' => 'ANH — Auditorías de medición',
                'titulo' => 'Evaluación de desempeño y buenas prácticas de medición',
                'reglamentacion' => 'Evaluar desempeño y buenas prácticas de medición de hidrocarburos en facilidades de almacenamiento.',
                'sanciones' => 'Sanciones de hasta 5.000 SMLMV, suspensión, revocatoria del contrato e inhabilitación temporal por manipular datos de medición.',
                'estacion_id' => null,
            ],
        ]);
    }
}
