<?php

// app/Notifications/Admin/UserRequestedPasswordReset.php
namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserRequestedPasswordReset extends Notification
{
    use Queueable;

    public function __construct(public User $user) {}

    public function via($notifiable): array
    {
        return ['mail']; // solo email; sin base de datos
    }

    public function toMail($notifiable): MailMessage
    {
        // Ajusta esta ruta al índice de tu módulo de usuarios
        $linkUsuarios = url('/admin/users?search=' . urlencode($this->user->email));

        return (new MailMessage)
            ->subject('Solicitud de restablecimiento de contraseña')
            ->greeting('Hola, Administrador(a)')
            ->line("El usuario {$this->user->name} ({$this->user->email}) solicitó restablecer su contraseña.")
            ->action('Abrir módulo de usuarios', $linkUsuarios)
            ->line('Por favor, actualiza la contraseña desde el módulo de usuarios.');
    }
}
