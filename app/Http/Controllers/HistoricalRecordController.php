<?php

namespace App\Http\Controllers;

use App\Models\DatosHistoricos;
use App\Models\Estacion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf; // barryvdh/laravel-dompdf

class HistoricalRecordController extends Controller
{
    public function index(Request $request)
    {
        // Estación fija del usuario
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

        // ===== meta de filtros =====
        $mode  = $request->input('mode', 'custom');            // custom | month | year
        $year  = (int) $request->input('year', (int) now()->year);
        $month = (int) $request->input('month', (int) now()->month);
        $from  = $request->input('from');                      // YYYY-MM-DD
        $to    = $request->input('to');                        // YYYY-MM-DD

        // Estructuras por defecto
        $series = [
            'labels'     => [],
            'inventario' => [],
            'psi_max'    => [],
            'cov_kg'     => [],
            'co2_kg'     => [],
        ];
        $kpis = [
            'cov_total_kg'          => 0,
            'co2_total_kg'          => 0,
            'factor_cov_to_co2'     => null,
            'inventario_ultimo_str' => '—',
        ];
        $noDataMsg = null;

        if (!$estacionId) {
            return view('content.historical-records.index', compact(
                'estacionNombre',
                'mode',
                'year',
                'month',
                'from',
                'to',
                'series',
                'kpis',
                'noDataMsg'
            ));
        }

        // Fechas disponibles para esta estación
        $minFecha  = DatosHistoricos::where('estacion_id', $estacionId)->min('fecha');
        $maxFecha  = DatosHistoricos::where('estacion_id', $estacionId)->max('fecha');
        $lastFecha = $maxFecha ? \Illuminate\Support\Carbon::parse($maxFecha) : null;

        // <<< AJUSTE: mes/año por defecto = último dato (afecta al SELECT aunque esté deshabilitado)
        if (!$request->has('month')) {
            $month = (int) ($lastFecha ? (int)$lastFecha->format('m') : (int)now()->format('m'));
        }
        if (!$request->has('year')) {
            $year  = (int) ($lastFecha ? (int)$lastFecha->format('Y') : (int)now()->format('Y'));
        }
        // >>> FIN AJUSTE

        // ===== Normalización de rango por modo (con “snap” a último periodo con datos) =====
        if ($mode === 'month') {
            $start = \Illuminate\Support\Carbon::createFromDate($year, $month, 1)->startOfDay();
            $end   = $start->copy()->endOfMonth();
        } elseif ($mode === 'year') {
            $start = \Illuminate\Support\Carbon::createFromDate($year, 1, 1)->startOfDay();
            $end   = $start->copy()->endOfYear();
        } else { // custom
            if (!$from || !$to) {
                $start = $minFecha ? \Illuminate\Support\Carbon::parse($minFecha)->startOfDay() : now()->copy()->startOfMonth();
                $end   = $maxFecha ? \Illuminate\Support\Carbon::parse($maxFecha)->endOfDay()   : now()->copy()->endOfMonth();
            } else {
                $start = \Illuminate\Support\Carbon::parse($from)->startOfDay();
                $end   = \Illuminate\Support\Carbon::parse($to)->endOfDay();
            }
        }

        // Normaliza a string para la vista
        $from = $start->toDateString();
        $to   = $end->toDateString();

        // Base del rango
        $base = DatosHistoricos::where('estacion_id', $estacionId)
            ->whereBetween('fecha', [$from, $to]);

        $hayDatosEnRango = $base->exists();

        if (!$hayDatosEnRango) {
            if ($lastFecha) {
                if ($mode === 'month' && !$request->has('month') && !$request->has('year')) {
                    $month = (int)$lastFecha->format('m');
                    $year  = (int)$lastFecha->format('Y');
                    $start = \Illuminate\Support\Carbon::createFromDate($year, $month, 1)->startOfDay();
                    $end   = $start->copy()->endOfMonth();
                } elseif ($mode === 'year' && !$request->has('year')) {
                    $year  = (int)$lastFecha->format('Y');
                    $start = \Illuminate\Support\Carbon::createFromDate($year, 1, 1)->startOfDay();
                    $end   = $start->copy()->endOfYear();
                }
                $from = $start->toDateString();
                $to   = $end->toDateString();
                $base = DatosHistoricos::where('estacion_id', $estacionId)->whereBetween('fecha', [$from, $to]);
                $hayDatosEnRango = $base->exists();
            }
        }

        if (!$hayDatosEnRango) {
            $noDataMsg = 'No se encontraron registros para el periodo seleccionado.';
            return view('content.historical-records.index', compact(
                'estacionNombre',
                'mode',
                'year',
                'month',
                'from',
                'to',
                'series',
                'kpis',
                'noDataMsg'
            ));
        }

        // ===== Agregación según modo (igual que ya tenías) =====
        if ($mode === 'year') {
            $grouped = $base->selectRaw("
            DATE_FORMAT(fecha, '%Y-%m') as periodo,
            AVG(volumen_gl)              AS inventario,
            MAX(presion_psi)             AS psi_max,
            SUM(perdidas_totales_cov_kg) AS cov_kg,
            SUM(cov_a_co2_kg)            AS co2_kg
        ")
                ->groupBy('periodo')->orderBy('periodo', 'asc')->get();
            $labels = $grouped->pluck('periodo')->all();
        } else {
            $grouped = $base->selectRaw("
            fecha as periodo,
            AVG(volumen_gl)              AS inventario,
            MAX(presion_psi)             AS psi_max,
            SUM(perdidas_totales_cov_kg) AS cov_kg,
            SUM(cov_a_co2_kg)            AS co2_kg
        ")
                ->groupBy('periodo')->orderBy('periodo', 'asc')->get();
            $labels = $grouped->pluck('periodo')->map(fn($d) => \Illuminate\Support\Carbon::parse($d)->format('Y-m-d'))->all();
        }

        $series['labels']     = $labels;
        $series['inventario'] = $grouped->pluck('inventario')->map(fn($v) => (float)$v)->all();
        $series['psi_max']    = $grouped->pluck('psi_max')->map(fn($v) => (float)$v)->all();
        $series['cov_kg']     = $grouped->pluck('cov_kg')->map(fn($v) => (float)$v)->all();
        $series['co2_kg']     = $grouped->pluck('co2_kg')->map(fn($v) => (float)$v)->all();

        // KPIs
        $sumCov = array_sum($series['cov_kg']);
        $sumCo2 = array_sum($series['co2_kg']);
        $kpis['cov_total_kg']      = $sumCov;
        $kpis['co2_total_kg']      = $sumCo2;
        $kpis['factor_cov_to_co2'] = $sumCov > 0 ? $sumCo2 / $sumCov : null;

        $u = DatosHistoricos::where('estacion_id', $estacionId)
            ->whereBetween('fecha', [$from, $to])
            ->orderBy('fecha', 'desc')->orderBy('hora', 'desc')->first();
        $kpis['inventario_ultimo_str'] = $u
            ? number_format((float)$u->volumen_gl, 2) . ' gl (' . $u->fecha . ')'
            : '—';

        return view('content.historical-records.index', compact(
            'estacionNombre',
            'mode',
            'year',
            'month',
            'from',
            'to',
            'series',
            'kpis',
            'noDataMsg'
        ));
    }



    public function pdf(Request $request)
    {
        // === Estación fija del usuario ===
        $user = Auth::user();
        $estacionId = (int) ($user?->estacion_id ?? 0);
        $estacionNombre = $estacionId
            ? (Estacion::where('id', $estacionId)->value('nombre') ?? '—')
            : '—';

        // === Filtros ===
        $mode  = $request->input('mode', 'custom'); // custom | month | year
        $year  = (int) $request->input('year', (int) now()->year);
        $month = (int) $request->input('month', (int) now()->month);
        $fromI = $request->input('from');  // YYYY-MM-DD
        $toI   = $request->input('to');    // YYYY-MM-DD

        // Valores por defecto para salida
        $series = [
            'labels'     => [],
            'inventario' => [],
            'psi_max' => [],
            'cov_kg'     => [],
            'co2_kg'     => [],
        ];
        $kpis = [
            'cov_total_kg' => 0,
            'co2_total_kg' => 0,
            'factor_cov_to_co2' => null,
            'inventario_ultimo_str' => '—',
        ];
        $noDataMsg = null;

        if (!$estacionId) {
            $logoPath = public_path('images/logo.png');
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
                'content.historical-records.pdf',
                compact('estacionNombre', 'mode', 'year', 'month', 'fromI', 'toI', 'series', 'kpis', 'logoPath', 'noDataMsg')
            )->setPaper('a4', 'portrait');
            return $pdf->download('reporte-historico-' . $estacionNombre . '.pdf');
        }

        // === Ventana de datos disponibles ===
        $minFecha  = DatosHistoricos::where('estacion_id', $estacionId)->min('fecha');
        $maxFecha  = DatosHistoricos::where('estacion_id', $estacionId)->max('fecha');
        $lastFecha = $maxFecha ? \Illuminate\Support\Carbon::parse($maxFecha) : null;

        // === Normalización de rango con “snap” a último periodo con datos ===
        if ($mode === 'month') {
            if ($lastFecha && !$request->has('month') && !$request->has('year')) {
                $month = (int) $lastFecha->format('m');
                $year  = (int) $lastFecha->format('Y');
            }
            $start = \Illuminate\Support\Carbon::createFromDate($year, $month, 1)->startOfDay();
            $end   = $start->copy()->endOfMonth();
        } elseif ($mode === 'year') {
            if ($lastFecha && !$request->has('year')) {
                $year = (int) $lastFecha->format('Y');
            }
            $start = \Illuminate\Support\Carbon::createFromDate($year, 1, 1)->startOfDay();
            $end   = $start->copy()->endOfYear();
        } else {
            if ($fromI && $toI) {
                $start = \Illuminate\Support\Carbon::parse($fromI)->startOfDay();
                $end   = \Illuminate\Support\Carbon::parse($toI)->endOfDay();
            } else {
                $start = $minFecha ? \Illuminate\Support\Carbon::parse($minFecha)->startOfDay() : now()->copy()->startOfMonth();
                $end   = $maxFecha ? \Illuminate\Support\Carbon::parse($maxFecha)->endOfDay()   : now()->copy()->endOfMonth();
            }
        }
        $from = $start->toDateString();
        $to   = $end->toDateString();

        $base = DatosHistoricos::where('estacion_id', $estacionId)->whereBetween('fecha', [$from, $to]);
        $hayDatos = $base->exists();

        if (!$hayDatos && $lastFecha) {
            if ($mode === 'month' && !$request->has('month') && !$request->has('year')) {
                $month = (int) $lastFecha->format('m');
                $year  = (int) $lastFecha->format('Y');
                $start = \Illuminate\Support\Carbon::createFromDate($year, $month, 1)->startOfDay();
                $end   = $start->copy()->endOfMonth();
            } elseif ($mode === 'year' && !$request->has('year')) {
                $year  = (int) $lastFecha->format('Y');
                $start = \Illuminate\Support\Carbon::createFromDate($year, 1, 1)->startOfDay();
                $end   = $start->copy()->endOfYear();
            }
            $from = $start->toDateString();
            $to   = $end->toDateString();
            $base = DatosHistoricos::where('estacion_id', $estacionId)->whereBetween('fecha', [$from, $to]);
            $hayDatos = $base->exists();
        }

        if (!$hayDatos) {
            $noDataMsg = 'No se encontraron registros para el periodo seleccionado.';
        } else {
            // === Agregación (anual->mensual, resto->diaria) ===
            if ($mode === 'year') {
                $grouped = $base->selectRaw("
                    DATE_FORMAT(fecha, '%Y-%m') AS periodo,
                    AVG(volumen_gl)              AS inventario,
                    MAX(presion_psi)             AS psi_max,
                    SUM(perdidas_totales_cov_kg) AS cov_kg,
                    SUM(cov_a_co2_kg)            AS co2_kg
                ")->groupBy('periodo')->orderBy('periodo')->get();
                $labels = $grouped->pluck('periodo')->all();
            } else {
                $grouped = $base->selectRaw("
                    fecha AS periodo,
                    AVG(volumen_gl)              AS inventario,
                    MAX(presion_psi)             AS psi_max,
                    SUM(perdidas_totales_cov_kg) AS cov_kg,
                    SUM(cov_a_co2_kg)            AS co2_kg
                ")->groupBy('periodo')->orderBy('periodo')->get();
                $labels = $grouped->pluck('periodo')->map(fn($d) => \Illuminate\Support\Carbon::parse($d)->format('Y-m-d'))->all();
            }

            $series = [
                'labels'     => $labels,
                'inventario' => $grouped->pluck('inventario')->map(fn($v) => (float)$v)->all(),
                'psi_max'    => $grouped->pluck('psi_max')->map(fn($v) => (float)$v)->all(),
                'cov_kg'     => $grouped->pluck('cov_kg')->map(fn($v) => (float)$v)->all(),
                'co2_kg'     => $grouped->pluck('co2_kg')->map(fn($v) => (float)$v)->all(),
            ];

            $sumCov = array_sum($series['cov_kg']);
            $sumCo2 = array_sum($series['co2_kg']);
            $kpis['cov_total_kg']      = $sumCov;
            $kpis['co2_total_kg']      = $sumCo2;
            $kpis['factor_cov_to_co2'] = $sumCov > 0 ? $sumCo2 / $sumCov : null;

            $u = DatosHistoricos::where('estacion_id', $estacionId)
                ->whereBetween('fecha', [$from, $to])
                ->orderBy('fecha', 'desc')->orderBy('hora', 'desc')->first();
            $kpis['inventario_ultimo_str'] = $u
                ? number_format((float)$u->volumen_gl, 2) . ' gl'
                : '—';
        }

        $logoPath = public_path('assets/img/logo/LOGO_ECO.png');
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'content.historical-records.pdf',
            compact(
                'estacionNombre',
                'mode',
                'year',
                'month',
                'from',
                'to',
                'series',
                'kpis',
                'logoPath',
                'noDataMsg'
            )
        )->setPaper('a4', 'portrait');

        return $pdf->download('reporte-historico-' . $estacionNombre . '.pdf');
    }
}
