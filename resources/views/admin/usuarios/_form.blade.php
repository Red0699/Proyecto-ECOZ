{{-- resources/views/admin/usuarios/_form.blade.php --}}

{{--
  Este formulario es inteligente. Detecta si una variable `$usuario` existe.
  - Si existe, entra en modo "edición".
  - Si no existe, se queda en modo "creación".
--}}

<form action="{{ isset($usuario) ? route('usuarios.update', $usuario) : route('usuarios.store') }}" method="POST">
    @csrf
    @if(isset($usuario))
    @method('PUT')
    @endif

    <div class="mb-3">
        <label class="form-label" for="name">Nombre</label>
        {{-- old() recupera el valor anterior si la validación falla. Si no, usa el valor del usuario o lo deja vacío. --}}
        <input type="text" class="form-control" id="name" name="name" placeholder="John Doe" value="{{ old('name', $usuario->name ?? '') }}" required />
        @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label class="form-label" for="email">Correo Electrónico</label>
        <input type="email" class="form-control" id="email" name="email" placeholder="john.doe@example.com" value="{{ old('email', $usuario->email ?? '') }}" required />
        @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror

        <div class="mb-3">
            <label class="form-label" for="role_id">Rol</label>
            <select id="role_id" name="role_id" class="form-select" required>
                <option value="">Selecciona un rol</option>
                @foreach($roles as $role)
                <option value="{{ $role->id }}" @if(old('role_id', $usuario->role_id ?? '') == $role->id) selected @endif>
                    {{ $role->display_name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3 form-password-toggle">
            <label class="form-label" for="password">Contraseña <small class="text-muted">{{ isset($usuario) ? '(Opcional)' : '' }}</small></label>
            <div class="input-group input-group-merge">
                <input type="password" id="password" class="form-control" name="password" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" @if(!isset($usuario)) required @endif />
                @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
            </div>
            @if(isset($usuario))
            <div class="form-text">Deja en blanco para no cambiar la contraseña.</div>
            @endif
        </div>

        <div class="mb-3 form-password-toggle">
            <label class="form-label" for="password_confirmation">Confirmar Contraseña</label>
            <div class="input-group input-group-merge">
                <input type="password" id="password_confirmation" class="form-control" name="password_confirmation" placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;" @if(!isset($usuario)) required @endif />
                <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
            </div>
        </div>

        <button type="submit" class="btn btn-primary">{{ isset($usuario) ? 'Actualizar Usuario' : 'Guardar Usuario' }}</button>
        <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
</form>