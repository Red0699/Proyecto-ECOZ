<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'current_password'      => ['required','current_password'], // valida contra el guard por defecto
            'password'              => ['required','string','min:8','confirmed'],
            'password_confirmation' => ['required'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Debes ingresar tu contraseña actual.',
            'current_password.current_password' => 'La contraseña actual no coincide.',
            'password.required' => 'La nueva contraseña es obligatoria.',
            'password.min'      => 'La nueva contraseña debe tener al menos 8 caracteres.',
            'password.confirmed'=> 'La confirmación no coincide.',
        ];
    }
}
