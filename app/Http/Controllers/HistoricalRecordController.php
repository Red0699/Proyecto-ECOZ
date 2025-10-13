<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DatosHistoricos as HistoricalData;
use App\Models\Estacion;
use Carbon\Carbon;

class HistoricalRecordController extends Controller
{
    public function index(Request $request)
    {
        $stationId = 1;

        // Recupera la última fecha disponible si no viene en el query string
        $latestDate = HistoricalData::where('estacion_id', $stationId)->max('fecha');
        // Normaliza la fecha objetivo al formato Y-m-d (para <input type="date">)
        $targetDate = $request->input('date', $latestDate);
        if ($targetDate instanceof \DateTimeInterface) {
            $targetDate = $targetDate->format('Y-m-d');
        }

        $recordsForDay = HistoricalData::where('estacion_id', $stationId)
            ->whereDate('fecha', $targetDate)
            ->orderBy('hora', 'asc')
            ->get();

        $stationName = Estacion::find($stationId)->nombre ?? 'Estación Desconocida';

        // Evita errores si no hay datos ese día
        $firstRow = $recordsForDay->first();

        // Promedio de humedad: si viene en 0–100, la pasamos a 0–1 para consistencia
        $avgHumidityRaw = $recordsForDay->avg('humedad_relativa'); // puede ser null
        $avgHumidity = $this->toUnitFraction($avgHumidityRaw);

        $kpis = [
            'avg_temp'        => $recordsForDay->avg('temperatura_ambiente_c') ?? 0,
            'avg_humidity'    => $avgHumidity ?? 0, // en fracción [0–1] para mantener tu blade
            'daily_sales'     => $firstRow->ventas_diarias_gl        ?? 0,
            'total_emissions' => $firstRow->emisiones_totales_voc_kg ?? 0,
        ];

        // --- Series para gráficas ---
        $octanePressures   = $recordsForDay->pluck('presion_sat_octano_mmhg');
        $heptanePressures  = $recordsForDay->pluck('presion_sat_heptano_mmhg');
        $toluenePressures  = $recordsForDay->pluck('presion_sat_tolueno_mmhg');

        $allPressures = $octanePressures->concat($heptanePressures)->concat($toluenePressures);
        $minPressure  = optional($allPressures)->min();
        $maxPressure  = optional($allPressures)->max();

        // Márgenes seguros si hay datos; si no, valores por defecto
        if (!is_null($minPressure) && !is_null($maxPressure)) {
            $minPressure = $minPressure - 2;
            $maxPressure = $maxPressure + 2;
        } else {
            $minPressure = 0;
            $maxPressure = 10;
        }

        $chartData = [
            // Etiquetas HH:mm desde 'hora'
            'time_labels'       => $recordsForDay->pluck('hora')
                ->map(fn($t) => Carbon::parse($t)->format('H:i')),

            // Mantén las KEYS esperadas por tu JS (no cambian)
            'temperatures'      => $recordsForDay->pluck('temperatura_ambiente_c'),
            'humidities'        => $recordsForDay->pluck('humedad_relativa')
                ->map(fn($h) => round($this->toUnitFraction($h) * 100, 2)),

            'octane_pressures'  => $octanePressures,
            'heptane_pressures' => $heptanePressures,
            'toluene_pressures' => $toluenePressures,
            'min_pressure'      => $minPressure,
            'max_pressure'      => $maxPressure,
        ];

        return view('content.historical-records.index', compact('kpis', 'chartData', 'targetDate', 'stationName'));
    }

    /**
     * Convierte humedad a fracción [0–1] si viene en 0–100; deja null si no hay dato.
     */
    private function toUnitFraction($value)
    {
        if ($value === null) return null;
        // Heurística sencilla: si es > 1 asumimos que está en %
        return ($value > 1) ? ($value / 100) : $value;
    }
}
