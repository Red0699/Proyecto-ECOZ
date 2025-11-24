<?php
namespace App\Support;

use App\Models\DatosHistoricos;
use App\Models\Estacion;
use Illuminate\Support\Carbon;

class AlertsBuilder
{
    public static function forUserNavbar($user): array
    {
        if (!$user) return [];

        // 1) Trae alertas del stream (solicitudes de restablecimiento, etc.)
        $streamAlerts = \App\Support\AlertStream::peekForUser((int)$user->id);

        // 2) Tus alertas “clásicas”
        $alerts = [];
        $now = now();

        $today = $now->toDateString();
        $alerts[] = [
            'id'        => "iso14001:{$today}",
            'severity'  => 'info',
            'norma'     => 'ISO 14001 — 4.4 SGA',
            'mensaje'   => 'Recuerda verificar que los procesos operativos cumplan los requisitos normativos según la norma ISO 14001.',
            'time_ago'  => 'Hoy',
            'sent_at'   => $now->toIso8601String(),
            'sent_at_str' => $now->format('Y-m-d H:i'),
        ];

        // (tu lógica de Decreto 1717/2008) …
        // $alerts[] = [ ... ];

        // 3) Mezcla + ordena por fecha desc
        $all = array_merge($streamAlerts, $alerts);
        usort($all, function($a,$b){
            return strcmp(($b['sent_at'] ?? ''), ($a['sent_at'] ?? ''));
        });

        return $all;
    }
}
