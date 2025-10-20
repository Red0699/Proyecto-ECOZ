<?php

namespace App\Http\Controllers;

use App\Models\DatosHistoricos;
use App\Models\Estacion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        // Estación desde usuario (o la más poblada como fallback)
        $user = Auth::user();
        $estacionId = (int)($user?->estacion_id ?? 0);
        if (!$estacionId) {
            $estacionId = (int) DatosHistoricos::select('estacion_id')
                ->groupBy('estacion_id')
                ->orderByRaw('COUNT(*) DESC')
                ->value('estacion_id') ?? 0;
        }

        $estacionNombre = $estacionId
            ? (Estacion::where('id', $estacionId)->value('nombre') ?? '—')
            : '—';

        // Capacidad por estación (fallback si no existe columna capacidad_gl)
        $capByName = ['Silvania' => 9000, 'Cota' => 12000, 'Ubaté' => 12000, 'Ubate' => 12000];
        $capDb = $estacionId ? Estacion::where('id', $estacionId)->value('capacidad_gl') : null;
        $capacidadTanque = (float)($capDb ?: ($capByName[$estacionNombre] ?? 12000));

        // Series para Inicio
        $series = [
            // “Todos los días”
            'days_all'     => [],   // YYYY-MM-DD
            'cov_all_days' => [],   // SUM(perdidas_totales_cov_kg)
            'psi_avg_days' => [],   // AVG(presion_psi)
        ];

        // KPIs
        $kpis = [
            'last_day_str'              => '—',
            'last_day_cov_kg'           => 0.0, 
            'last_day_co2_kg'           => 0.0, 
            'last_day_variacion_gl'     => 0.0, 
            'last_day_variacion_eds_gl' => 0.0,
            'last_day_variacion_eds_gl'  => 0.0,
            'last_lote'                 => null, 
            // Tanque
            'inv_ult_gl'                => 0.0,  
            'inv_capacity_gl'           => $capacidadTanque,
        ];

        $alerts = [];

        if ($estacionId) {
            $base = DatosHistoricos::where('estacion_id', $estacionId);

            // ===== A) Agregados por día (“todos los días”) =====
            $aggAll = (clone $base)
                ->selectRaw("
                    fecha,
                    SUM(perdidas_totales_cov_kg) AS cov_sum,
                    AVG(presion_psi)              AS psi_avg
                ")
                ->groupBy('fecha')
                ->orderBy('fecha', 'asc')
                ->get();

            $series['days_all']     = $aggAll->pluck('fecha')
                ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->values()->all();
            $series['cov_all_days'] = $aggAll->pluck('cov_sum')->map(fn($v) => (float)$v)->values()->all();
            $series['psi_avg_days'] = $aggAll->pluck('psi_avg')->map(fn($v) => (float)$v)->values()->all();

            // ===== B) Último día registrado (global) =====
            $lastFecha = (clone $base)->max('fecha');
            if ($lastFecha) {
                $kpis['last_day_str'] = Carbon::parse($lastFecha)->format('Y-m-d');

                $kAgg = (clone $base)
                    ->whereDate('fecha', $lastFecha)
                    ->selectRaw("
                        AVG(perdidas_totales_cov_kg) AS cov_sum,
                        AVG(cov_a_co2_kg)            AS co2_sum,
                        AVG(sumatoria_variacion_gl)  AS variacion_gl,
                        AVG(variacion_eds_gl)        AS variacion_eds_gl,
                        AVG(perdidas_operacion_kg)   AS operacion_cov_sum
                    ")
                    ->first();

                $kpis['last_day_cov_kg']           = (float)($kAgg->cov_sum ?? 0);
                $kpis['last_day_co2_kg']           = (float)($kAgg->co2_sum ?? 0);
                $kpis['last_day_variacion_gl']     = (float)($kAgg->variacion_gl ?? 0);
                $kpis['last_day_variacion_eds_gl'] = (float)($kAgg->variacion_eds_gl ?? 0);
                $kpis['last_day_operacion_cov_kg']  = (float)($kAgg->operacion_cov_sum ?? 0);

                // Último registro del día (para inventario)
                $invUlt = (clone $base)
                    ->whereDate('fecha', $lastFecha)
                    ->orderByRaw('CASE WHEN hora IS NULL OR hora = "" THEN 1 ELSE 0 END')
                    ->orderBy('hora', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();

                $kpis['inv_ult_gl'] = (int)($invUlt->volumen_gl ?? 0);

                // === Regla Decreto 1717/2008 Art. 26 (usar inventario de la ÚLTIMA HORA) ===
                $UMBRAL = 2700.0;

                // Reutiliza $invUlt obtenido arriba
                $invUltRow = $invUlt;
                $invUltGl   = (float)($invUltRow->volumen_gl ?? 0);
                $lastHora   = $invUltRow?->hora ? Carbon::parse($invUltRow->hora)->format('H:i') : '—';
                $lastFechaS = $kpis['last_day_str']; // YYYY-MM-DD (ya calculado arriba)

                // actualiza el KPI que ya muestras en la tarjeta
                $kpis['inv_ult_gl'] = $invUltGl;

                if ($invUltGl > 0 && $invUltGl < $UMBRAL) {
                    // Mensaje para la card de notificaciones
                    $alerts[] = sprintf(
                        'Decreto 1717/2008 (Art. 26): Inventario bajo en %s %s — %.2f gl (< %d gl). Acción: abastecer e informar.',
                        $lastFechaS,
                        $lastHora,
                        $invUltGl,
                        (int)$UMBRAL
                    );

                    // Enviar correo (evitar duplicados por día y estación)
                    if ($user && $user->email) {
                        $cacheKey = "invbajo1717:lasthour:estacion:{$estacionId}:fecha:{$lastFechaS}";
                        $alreadySent = \Illuminate\Support\Facades\Cache::get($cacheKey, false);

                        if (!$alreadySent) {
                            \Illuminate\Support\Facades\Notification::send($user, new \App\Notifications\InventarioBajoDecreto1717(
                                estacionNombre: $estacionNombre,
                                fechaStr: "{$lastFechaS} {$lastHora}",
                                inventarioDiarioGl: $invUltGl,
                                umbralGl: $UMBRAL
                            ));
                            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->addHours(24));
                        }
                    }
                }
            }

            // (Opcional) último lote para display
            $lastRow = (clone $base)->orderBy('created_at', 'desc')->first();
            if ($lastRow) $kpis['last_lote'] = $lastRow->lote_id;
        }

        // === ISO 14001 - 4.4 SGA (recordatorio DIARIO y fijo en Inicio) ===
        $isoTitle   = 'ISO 14001 — 4.4 SGA';
        $isoMessage = 'Recuerda verificar que los procesos operativos cumplan los requisitos normativos según la norma ISO 14001.';

        // 2.a) Mostrar SIEMPRE en la card de notificaciones (fijo)
        $alerts[] = [
            'severity' => 'info',
            'norma'    => $isoTitle,
            'mensaje'  => $isoMessage,
        ];

        // 2.b) Enviar UNA vez por día (por usuario/estación)
        if ($user && $user->email && $estacionId) {
            $today = now()->toDateString(); // YYYY-MM-DD
            $cacheKeyIso = "iso14001:daily:estacion:{$estacionId}:user:{$user->id}:{$today}";
            if (!\Illuminate\Support\Facades\Cache::get($cacheKeyIso, false)) {
                \Illuminate\Support\Facades\Notification::send(
                    $user,
                    new \App\Notifications\Iso14001DailyReminder(
                        estacionNombre: $estacionNombre,
                        fechaStr: $today
                    )
                );
                \Illuminate\Support\Facades\Cache::put($cacheKeyIso, true, now()->addHours(24));
            }
        }

        return view('content.home.index', compact('estacionNombre', 'series', 'kpis', 'alerts'));
    }
}
