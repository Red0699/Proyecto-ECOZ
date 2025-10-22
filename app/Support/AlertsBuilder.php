<?php

namespace App\Support;

use App\Models\DatosHistoricos;
use App\Models\Estacion;
use Illuminate\Support\Carbon;

class AlertsBuilder
{
    /**
     * Devuelve un array de alertas para el usuario (navbar).
     * Cada alerta:
     *  - id        : string único para el día (para “marcar como visto” en front, si quieres)
     *  - severity  : danger|warning|info
     *  - norma     : título corto
     *  - mensaje   : texto corto
     *  - time      : string amigable (e.g. "hoy", "ayer", "1h")
     */
    public static function forUserNavbar($user): array
    {
        if (!$user) return [];

        $estacionId = (int) ($user->estacion_id ?? 0);
        if (!$estacionId) {
            $estacionId = (int) DatosHistoricos::select('estacion_id')
                ->groupBy('estacion_id')
                ->orderByRaw('COUNT(*) DESC')
                ->value('estacion_id') ?? 0;
        }
        $estacionNombre = $estacionId
            ? (Estacion::where('id', $estacionId)->value('nombre') ?? '—')
            : '—';

        $alerts = [];

        $now = now();

        // ISO 14001 — recordatorio diario fijo
        $today = $now->toDateString();
        $alerts[] = [
            'id'        => "iso14001:{$today}",
            'severity'  => 'info',
            'norma'     => 'ISO 14001 — 4.4 SGA',
            'mensaje'   => 'Recuerda verificar que los procesos operativos cumplan los requisitos normativos según la norma ISO 14001.',
            'time_ago'  => 'Hoy',                          // relativo corto
            'sent_at'   => $now->toIso8601String(),        // ISO para parse seguro en front
            'sent_at_str' => $now->format('Y-m-d H:i'),    // exacto para mostrar
        ];

        // Decreto 1717/2008 — inventario última hora < 2700
        if ($estacionId) {
            $base = DatosHistoricos::where('estacion_id', $estacionId);
            $lastFecha = (clone $base)->max('fecha');

            if ($lastFecha) {
                $invUlt = (clone $base)
                    ->whereDate('fecha', $lastFecha)
                    ->orderByRaw('CASE WHEN hora IS NULL OR hora = "" THEN 1 ELSE 0 END')
                    ->orderBy('hora', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($invUlt) {
                    $UMBRAL = 2700.0;
                    $invUltGl = (float) ($invUlt->volumen_gl ?? 0);
                    if ($invUltGl > 0 && $invUltGl < $UMBRAL) {
                        $sent = Carbon::parse("{$lastFecha} {$invUlt->hora}");
                        $horaStr = $invUlt->hora ? Carbon::parse($invUlt->hora)->format('H:i') : '—';

                        $alerts[] = [
                            'id'        => "dec1717:{$lastFecha}",
                            'severity'  => 'danger',
                            'norma'     => 'Decreto 1717/2008 — Art. 26',
                            'mensaje'   => "Inventario bajo {$invUltGl} gl (< 2700) — {$estacionNombre} · {$lastFecha} {$horaStr}.",
                            'time_ago'  => $sent->diffForHumans(null, true) ?: 'Hoy',
                            'sent_at'   => $sent->toIso8601String(),
                            'sent_at_str' => $sent->format('Y-m-d H:i'),
                        ];
                    }
                }
            }
        }

        return $alerts;
    }
}
