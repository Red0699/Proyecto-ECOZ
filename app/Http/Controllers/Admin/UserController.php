<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\Role;
use App\Models\Estacion; // 👈 importar
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q   = $request->string('q')->toString();
        $per = (int) $request->input('per_page', 10);

        $users = User::with(['role', 'estacion'])
            ->when($q, function ($qr) use ($q) {
                $qr->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhereHas('estacion', fn($e) => $e->where('nombre', 'like', "%{$q}%"));
                });
            })
            ->orderByDesc('id')
            ->paginate($per);

        return view('admin.usuarios.index', compact('users'));
    }


    public function create()
    {
        $roles = Role::all();
        $estaciones = Estacion::orderBy('nombre')->get(); // 👈 cargar estaciones
        return view('admin.usuarios.create', compact('roles', 'estaciones'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users,email',
            'password'  => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id'   => 'required|exists:roles,id',
            'estacion_id' => 'required|integer|exists:estaciones,id',
        ]);

        User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role_id'     => $request->role_id,
            'estacion_id' => $request->estacion_id,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado exitosamente.');
    }

    public function edit(User $usuario)
    {
        $roles = Role::all();
        $estaciones = Estacion::orderBy('nombre')->get();
        return view('admin.usuarios.edit', compact('usuario', 'roles', 'estaciones'));
    }

    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users,email,' . $usuario->id,
            'role_id'   => 'required|exists:roles,id',
            'estacion_id' => 'required|integer|exists:estaciones,id',
        ]);

        $usuario->update([
            'name'        => $request->name,
            'email'       => $request->email,
            'role_id'     => $request->role_id,
            'estacion_id' => $request->estacion_id,
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $usuario->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado exitosamente.');
    }

    public function destroy(User $usuario)
    {
        if (Auth::user()->id == $usuario->id) {
            return back()->with('error', 'No puedes eliminar tu propio usuario administrador.');
        }
        $usuario->delete();
        return back()->with('success', 'Usuario eliminado exitosamente.');
    }
}
