<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
// use Illuminate\Contracts\Queue\ShouldQueue;

class Iso14001DailyReminder extends Notification // implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $estacionNombre,
        public string $fechaStr // YYYY-MM-DD
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('ECO₂Z — Recordatorio diario ISO 14001 (4.4 SGA)')
            ->greeting('Hola, '.$notifiable->name)
            ->line('Estación: '.$this->estacionNombre)
            ->line('Fecha: '.$this->fechaStr)
            ->line('Recuerda verificar que los procesos operativos cumplan los requisitos normativos según la norma ISO 14001.')
            ->salutation('Equipo ECO₂Z');
    }
}
