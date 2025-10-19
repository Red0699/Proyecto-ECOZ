<?php

namespace App\Http\Controllers;

use App\Models\DatosHistoricos;
use App\Models\Estacion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class HistoricalRecordController extends Controller
{
    public function index(Request $request)
    {
        // 1) Estación desde el usuario autenticado (sin select).
        $user = Auth::user();
        $estacionId = (int) ($user?->estacion_id ?? 0);

        // Fallback: si el usuario no tiene estación, toma la primera con datos.
        if (!$estacionId) {
            $estacionId = (int) DatosHistoricos::select('estacion_id')
                ->groupBy('estacion_id')
                ->orderByRaw('COUNT(*) DESC')
                ->value('estacion_id') ?? 0;
        }

        $estacionNombre = $estacionId
            ? (Estacion::where('id', $estacionId)->value('nombre') ?? '—')
            : '—';

        // 2) Filtros de fecha (YYYY-MM-DD)
        $from = $request->input('from');
        $to   = $request->input('to');

        // Defaults si no llegan filtros o no hay datos
        $series = [
            'labels'       => [],
            'inventario'   => [],
            'psi_max'      => [],
            'cov_kg'       => [],
            'co2_kg'       => [],
            'variacion_gl' => [],
            'ventas_gl'    => [],
            'descargue_gl' => [],
        ];
        $kpis = [
            'cov_total_kg'        => 0,
            'co2_total_kg'        => 0,
            'factor_cov_to_co2'   => null,
            'inventario_ultimo_str' => '—',
        ];
        $alerts = [];

        if ($estacionId) {
            $base = DatosHistoricos::where('estacion_id', $estacionId);

            // Si no viene rango, usar min/max disponibles de esa estación
            if (!$from || !$to) {
                $minFecha = (clone $base)->min('fecha');
                $maxFecha = (clone $base)->max('fecha');
                $from = $from ?: ($minFecha ? Carbon::parse($minFecha)->toDateString() : Carbon::today()->toDateString());
                $to   = $to   ?: ($maxFecha ? Carbon::parse($maxFecha)->toDateString() : Carbon::today()->toDateString());
            }

            // 3) Agregado por día en el rango
            $grouped = (clone $base)
                ->whereBetween('fecha', [$from, $to])
                ->selectRaw("
                    fecha,
                    AVG(volumen_gl)                        AS inventario,
                    MAX(presion_psi)                       AS psi_max,
                    SUM(perdidas_totales_cov_kg)           AS cov_kg,
                    SUM(cov_a_co2_kg)                      AS co2_kg,
                    SUM(sumatoria_variacion_gl)            AS variacion_gl,
                    SUM(ventas_diarias_gl)                 AS ventas_gl,
                    SUM(descargue_combustible_gl)          AS descargue_gl
                ")
                ->groupBy('fecha')
                ->orderBy('fecha','asc')
                ->get();

            // 4) Labels SOLO fecha y series reindexadas a 0..n (->values()->all())
            $labels = $grouped->pluck('fecha')
                ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                ->values()->all();

            $series = [
                'labels'       => $labels,
                'inventario'   => $grouped->pluck('inventario')->map(fn($v)=> round((float)$v, 4))->values()->all(),
                'psi_max'      => $grouped->pluck('psi_max')->map(fn($v)=> round((float)$v, 4))->values()->all(),
                'cov_kg'       => $grouped->pluck('cov_kg')->map(fn($v)=> round((float)$v, 6))->values()->all(),
                'co2_kg'       => $grouped->pluck('co2_kg')->map(fn($v)=> round((float)$v, 6))->values()->all(),
                'variacion_gl' => $grouped->pluck('variacion_gl')->map(fn($v)=> round((float)$v, 4))->values()->all(),
                'ventas_gl'    => $grouped->pluck('ventas_gl')->map(fn($v)=> round((float)$v, 4))->values()->all(),
                'descargue_gl' => $grouped->pluck('descargue_gl')->map(fn($v)=> round((float)$v, 4))->values()->all(),
            ];

            // 5) KPIs del rango
            $sumCov = array_sum($series['cov_kg']);
            $sumCo2 = array_sum($series['co2_kg']);
            $kpis['cov_total_kg']      = $sumCov;
            $kpis['co2_total_kg']      = $sumCo2;
            $kpis['factor_cov_to_co2'] = $sumCov > 0 ? $sumCo2 / $sumCov : null;

            // 6) Último inventario del rango (último registro real)
            $u = (clone $base)->whereBetween('fecha', [$from, $to])
                ->orderBy('fecha','desc')->orderBy('hora','desc')->first();
            $kpis['inventario_ultimo_str'] = $u
                ? number_format((float)$u->volumen_gl, 2).' gl ('.$u->fecha.')'
                : '—';
        } else {
            // Rango por defecto para el form si no hay estación
            $from = $from ?: Carbon::today()->toDateString();
            $to   = $to   ?: Carbon::today()->toDateString();
        }

        return view('content.historical-records.index', compact(
            'estacionNombre', 'from', 'to', 'series', 'kpis', 'alerts'
        ));
    }
}
