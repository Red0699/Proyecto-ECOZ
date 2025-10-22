<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'name'  => ['required','string','max:255'],
            'email' => [
                'required','email','max:255',
                Rule::unique('users','email')->ignore($this->user()->id)
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'  => 'El nombre es obligatorio.',
            'email.required' => 'El correo es obligatorio.',
            'email.email'    => 'Ingresa un correo válido.',
            'email.unique'   => 'Este correo ya está registrado.',
        ];
    }
}
