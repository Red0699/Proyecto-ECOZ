<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
// use Illuminate\Contracts\Queue\ShouldQueue;

class InventarioBajoDecreto1717 extends Notification // implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $estacionNombre,
        public string $fechaStr,            // ahora puede venir "YYYY-MM-DD HH:mm"
        public float $inventarioDiarioGl,   // reutilizamos el nombre, pero ahora enviamos el valor de última hora
        public float $umbralGl = 2700.0
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ECO₂Z — Alerta: Inventario bajo (Decreto 1717/2008, Art. 26)')
            ->greeting('Hola, '.$notifiable->name)
            ->line('Se detectó inventario bajo en la estación: '.$this->estacionNombre.'.')
            ->line('Fecha y hora (último registro): '.$this->fechaStr)
            ->line('Inventario (última hora): '.number_format($this->inventarioDiarioGl, 2).' gl')
            ->line('Umbral normativo: '.number_format($this->umbralGl, 0).' gl')
            ->line('Norma: Decreto 1717 de 2008, Artículo 26.')
            ->line('Acción sugerida: Abastecer el tanque y registrar el evento para evitar ingreso de aire.')
            ->salutation('Equipo ECO₂Z');
    }
}
