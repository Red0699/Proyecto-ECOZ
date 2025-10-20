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
        // 1) Estación desde el usuario autenticado (sin select)
        $user = Auth::user();
        $estacionId = (int) ($user?->estacion_id ?? 0);

        if (!$estacionId) {
            $estacionId = (int) DatosHistoricos::select('estacion_id')
                ->groupBy('estacion_id')
                ->orderByRaw('COUNT(*) DESC')
                ->value('estacion_id') ?? 0;
        }

        $estacionNombre = $estacionId
            ? (Estacion::where('id', $estacionId)->value('nombre') ?? '—')
            : '—';

        // 2) Filtros de fecha (para las gráficas "por rango" que mantienes)
        $from = $request->input('from');
        $to   = $request->input('to');

        // Valores por defecto
        $series = [
            'labels'       => [],
            'inventario'   => [],
            'psi_max'      => [],
            'cov_kg'       => [],
            'co2_kg'       => [],
            'variacion_gl' => [],
            'ventas_gl'    => [],
            'descargue_gl' => [],

            // NUEVO: series horarias del "último día del último archivo (lote)"
            'day_labels'   => [],
            'presion_dia'  => [],
            'cov_dia'      => [],
            'co2_dia'      => [],
            'resp_dia'     => [],
            'oper_dia'     => [],
        ];

        $kpis = [
            'cov_total_kg'          => 0,
            'co2_total_kg'          => 0,
            'factor_cov_to_co2'     => null,
            'inventario_ultimo_str' => '—',
        ];

        $alerts = [];

        if ($estacionId) {
            $base = DatosHistoricos::where('estacion_id', $estacionId);

            // ====== Parte A: RANGO (agregado por día) – se mantiene ======
            if (!$from || !$to) {
                $minFecha = (clone $base)->min('fecha');
                $maxFecha = (clone $base)->max('fecha');
                $from = $from ?: ($minFecha ? Carbon::parse($minFecha)->toDateString() : Carbon::today()->toDateString());
                $to   = $to   ?: ($maxFecha ? Carbon::parse($maxFecha)->toDateString() : Carbon::today()->toDateString());
            }

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

            $labels = $grouped->pluck('fecha')
                ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
                ->values()->all();

            $series['labels']       = $labels;
            $series['inventario']   = $grouped->pluck('inventario')->map(fn($v)=>(float)$v)->values()->all();
            $series['psi_max']      = $grouped->pluck('psi_max')->map(fn($v)=>(float)$v)->values()->all();
            $series['cov_kg']       = $grouped->pluck('cov_kg')->map(fn($v)=>(float)$v)->values()->all();
            $series['co2_kg']       = $grouped->pluck('co2_kg')->map(fn($v)=>(float)$v)->values()->all();
            $series['variacion_gl'] = $grouped->pluck('variacion_gl')->map(fn($v)=>(float)$v)->values()->all();
            $series['ventas_gl']    = $grouped->pluck('ventas_gl')->map(fn($v)=>(float)$v)->values()->all();
            $series['descargue_gl'] = $grouped->pluck('descargue_gl')->map(fn($v)=>(float)$v)->values()->all();

            $sumCov = array_sum($series['cov_kg']);
            $sumCo2 = array_sum($series['co2_kg']);
            $kpis['cov_total_kg']      = $sumCov;
            $kpis['co2_total_kg']      = $sumCo2;
            $kpis['factor_cov_to_co2'] = $sumCov > 0 ? $sumCo2 / $sumCov : null;

            $u = (clone $base)->whereBetween('fecha', [$from, $to])
                ->orderBy('fecha','desc')->orderBy('hora','desc')->first();
            $kpis['inventario_ultimo_str'] = $u
                ? number_format((float)$u->volumen_gl, 2).' gl ('.$u->fecha.')'
                : '—';

            // ====== Parte B: ÚLTIMO DÍA DEL ÚLTIMO ARCHIVO (lote) ======
            $lastRow = (clone $base)->orderBy('created_at', 'desc')->first();
            if ($lastRow) {
                $lastLote  = $lastRow->lote_id;
                // dentro de ese lote, el día más reciente
                $lastFecha = (clone $base)->where('lote_id', $lastLote)->max('fecha');

                $dayRows = (clone $base)
                    ->where('lote_id', $lastLote)
                    ->whereDate('fecha', $lastFecha)
                    ->orderBy('hora','asc')
                    ->get();

                // etiquetas: hora (HH:mm) si existe; si no, índice
                $dayLabels = $dayRows->map(function ($r, $idx) {
                    return $r->hora ? substr((string)$r->hora, 0, 5) : ('P' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT));
                })->values();

                $series['day_labels']  = $dayLabels->all();
                $series['presion_dia'] = $dayRows->pluck('presion_psi')->map(fn($v)=>(float)$v)->values()->all();
                $series['cov_dia']     = $dayRows->pluck('perdidas_totales_cov_kg')->map(fn($v)=>(float)$v)->values()->all();
                $series['co2_dia']     = $dayRows->pluck('cov_a_co2_kg')->map(fn($v)=>(float)$v)->values()->all();
                $series['resp_dia']    = $dayRows->pluck('perdidas_respiracion_kg')->map(fn($v)=>(float)$v)->values()->all();
                $series['oper_dia']    = $dayRows->pluck('perdidas_operacion_kg')->map(fn($v)=>(float)$v)->values()->all();
            }
        } else {
            $from = $from ?: Carbon::today()->toDateString();
            $to   = $to   ?: Carbon::today()->toDateString();
        }

        return view('content.historical-records.index', compact(
            'estacionNombre', 'from', 'to', 'series', 'kpis', 'alerts'
        ));
    }
}
