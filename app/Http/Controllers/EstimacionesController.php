<?php

// app/Http/Controllers/EstimacionesController.php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class EstimacionesController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $estacion = $user?->estacion?->nombre;

        if (!$estacion) {
            // Sin estación asignada
            return view('estimaciones.visual', [
                'estacion' => null,
                'rows'     => [],
                'fe'       => 1.24,
            ]);
        }

        // Datos del Word (estáticos)
        $fe = 1.24; // lb/1000 gal
        $tablaCotaUbaté = [
            ['volumen' => 3600,  'estacion' => 'Cota',   'emision' => 2.024],
            ['volumen' => 4600,  'estacion' => 'Ubaté',  'emision' => 2.587],
            ['volumen' => 5600,  'estacion' => 'Cota',   'emision' => 3.149],
            ['volumen' => 6600,  'estacion' => 'Ubaté',  'emision' => 3.712],
            ['volumen' => 7600,  'estacion' => 'Cota',   'emision' => 4.274],
            ['volumen' => 8600,  'estacion' => 'Ubaté',  'emision' => 4.837],
            ['volumen' => 9600,  'estacion' => 'Cota',   'emision' => 5.399],
            ['volumen' => 10600, 'estacion' => 'Ubaté',  'emision' => 5.962],
            ['volumen' => 11600, 'estacion' => 'Cota',   'emision' => 6.524],
        ];
        $tablaSilvania = [
            ['volumen' => 2700, 'estacion' => 'Silvania', 'emision' => 1.518],
            ['volumen' => 3700, 'estacion' => 'Silvania', 'emision' => 2.081],
            ['volumen' => 4700, 'estacion' => 'Silvania', 'emision' => 2.643],
            ['volumen' => 5700, 'estacion' => 'Silvania', 'emision' => 3.205],
            ['volumen' => 6700, 'estacion' => 'Silvania', 'emision' => 3.768],
            ['volumen' => 7700, 'estacion' => 'Silvania', 'emision' => 4.330],
            ['volumen' => 8700, 'estacion' => 'Silvania', 'emision' => 4.893],
        ];

        // Selecciona y filtra según la estación del usuario
        if ($estacion === 'Silvania') {
            $rows = $tablaSilvania;
        } else {
            // Para Cota o Ubaté: toma sólo las filas correspondientes
            $rows = array_values(array_filter($tablaCotaUbaté, fn($r) => $r['estacion'] === $estacion));
        }

        $vols = array_map(fn($r) => $r['volumen'], $rows);
        $emis = array_map(fn($r) => $r['emision'], $rows);

        $stats = [
            'filas'      => count($rows),
            'vol_total'  => array_sum($vols),
            'cov_total'  => round(array_sum($emis), 3),
            'vol_max'    => $vols ? max($vols) : 0,
            'vol_min'    => $vols ? min($vols) : 0,
        ];

        return view('content.estimaciones.index', compact('estacion','rows','fe','stats'));
    }
}
