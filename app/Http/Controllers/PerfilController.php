<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PerfilController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();

        // Si usas verificación de correo (MustVerifyEmail), podrías mostrar estado aquí.
        return view('content.profile.edit', [
            'user' => $user,
        ]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();

        $emailChanged = $request->email !== $user->email;

        $user->name  = $request->name;
        $user->email = $request->email;

        // Si cambió el correo y usas verificación, invalida verificación anterior.
        if (interface_exists(\Illuminate\Contracts\Auth\MustVerifyEmail::class) && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && $emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Si usas verificación de correo:
        if ($emailChanged && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
            $user->sendEmailVerificationNotification();
            return back()->with('success', 'Perfil actualizado. Te enviamos un correo para verificar tu nueva dirección.');
        }

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = $request->user();

        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success_password', 'Contraseña actualizada correctamente.');
    }
}
