<?php
namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class AlertStream
{
    // clave por usuario: alerts:navbar:{user_id}
    protected static function keyFor(int $userId): string
    {
        return "alerts:navbar:{$userId}";
    }

    /** Empuja 1 alerta a 1 usuario */
    public static function pushForUser(int $userId, array $alert, int $ttlMinutes = 1440): void
    {
        $key = self::keyFor($userId);
        $list = Cache::get($key, []);

        // normaliza timestamps
        $now = Carbon::now();
        $alert['sent_at']     = $alert['sent_at']     ?? $now->toIso8601String();
        $alert['sent_at_str'] = $alert['sent_at_str'] ?? $now->format('Y-m-d H:i');

        // id obligatorio para evitar duplicados visuales
        $alert['id'] = $alert['id'] ?? md5(($alert['norma'] ?? 'alert')."|".$alert['sent_at']);

        $list[] = $alert;

        // ordena desc por fecha de envío
        usort($list, function($a,$b){
            return strcmp($b['sent_at'] ?? '', $a['sent_at'] ?? '');
        });

        Cache::put($key, $list, now()->addMinutes($ttlMinutes));
    }

    /** Empuja 1 alerta a TODOS los admins (role_id = 1) */
    public static function pushForAdmins(array $alert, int $ttlMinutes = 1440): void
    {
        $admins = \App\Models\User::where('role_id', 1)
            ->whereNotNull('email')
            ->pluck('id');

        foreach ($admins as $adminId) {
            self::pushForUser((int)$adminId, $alert, $ttlMinutes);
        }
    }

    /** Lee sin consumir (deja la lista intacta) */
    public static function peekForUser(int $userId): array
    {
        return Cache::get(self::keyFor($userId), []);
    }

    /** Opcional: consumir y vaciar */
    public static function pullForUser(int $userId): array
    {
        $key = self::keyFor($userId);
        $list = Cache::get($key, []);
        Cache::forget($key);
        return $list;
    }
}
