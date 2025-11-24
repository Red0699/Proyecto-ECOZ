<?php
// app/Http/Controllers/ForgotController.php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use App\Notifications\UserRequestedPasswordReset;
use App\Support\AlertStream;

class ForgotController extends Controller
{
    public function show()
    {
        return view('content.authentications.auth-forgot-password-basic');
    }

    public function submit(Request $request)
    {
        // Validación estricta: el email debe existir en users.email
        $data = $request->validate(
            ['email' => ['required','email','exists:users,email']],
            ['email.exists' => 'Este correo no está registrado en el sistema.']
        );

        // Ya validado que existe:
        $user = User::where('email', $data['email'])->firstOrFail();

        // Notificar a administradores (role_id = 1) por correo (o el canal que uses)
        $admins = User::where('role_id', 1)->whereNotNull('email')->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new UserRequestedPasswordReset($user));
        }

        \App\Support\AlertStream::pushForAdmins([
            'severity' => 'info',
            'norma'    => 'Solicitud de restablecimiento',
            'mensaje'  => "El usuario {$user->name} ({$user->email}) solicitó actualizar su contraseña.",
            // opcional: 'id' => 'forgot:'.md5(...)
        ], 1440);

        return back()->with('status', 'Solicitud enviada al administrador para gestionar el restablecimiento.');
    }
}
