
<form action="{{ isset($usuario) ? route('usuarios.update', $usuario) : route('usuarios.store') }}" method="POST" novalidate>
  @csrf
  @if(isset($usuario)) @method('PUT') @endif

  {{-- Nombre --}}
  <div class="mb-3">
    <label class="form-label" for="name">Nombre</label>
    <input
      type="text"
      id="name"
      name="name"
      placeholder="John Doe"
      value="{{ old('name', $usuario->name ?? '') }}"
      class="form-control @error('name') is-invalid @enderror"
      required
    />
    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- Email --}}
  <div class="mb-3">
    <label class="form-label" for="email">Correo Electrónico</label>
    <input
      type="email"
      id="email"
      name="email"
      placeholder="john.doe@example.com"
      value="{{ old('email', $usuario->email ?? '') }}"
      class="form-control @error('email') is-invalid @enderror"
      required
      autocomplete="username"
    />
    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- Rol --}}
  <div class="mb-3">
    <label class="form-label" for="role_id">Rol</label>
    <select id="role_id" name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
      <option value="">Selecciona un rol</option>
      @foreach($roles as $role)
        <option value="{{ $role->id }}" @selected(old('role_id', $usuario->role_id ?? '') == $role->id)>
          {{ $role->display_name }}
        </option>
      @endforeach
    </select>
    @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- Estación --}}
  <div class="mb-3">
    <label class="form-label" for="estacion_id">Estación asignada</label>
    <select id="estacion_id" name="estacion_id" class="form-select @error('estacion_id') is-invalid @enderror">
      <option value="">— Sin asignar —</option>
      @foreach($estaciones as $estacion)
        <option value="{{ $estacion->id }}" @selected(old('estacion_id', $usuario->estacion_id ?? '') == $estacion->id)>
          {{ $estacion->nombre }}
        </option>
      @endforeach
    </select>
    <div class="form-text">Opcional. Úsalo para ligar el usuario a una EDS.</div>
    @error('estacion_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
  </div>

  {{-- Contraseña --}}
  <div class="mb-3 form-password-toggle">
    <label class="form-label" for="password">
      Contraseña
      <small class="text-muted">{{ isset($usuario) ? '(Opcional)' : '' }}</small>
    </label>
    <div class="input-group input-group-merge">
      <input
        type="password"
        id="password"
        name="password"
        class="form-control @error('password') is-invalid @enderror"
        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
        @unless(isset($usuario)) required @endunless
        autocomplete="new-password"
      />
      <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
      @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>
    @isset($usuario)
      <div class="form-text">Déjalo en blanco para no cambiar la contraseña.</div>
    @endisset
  </div>

  {{-- Confirmación --}}
  <div class="mb-3 form-password-toggle">
    <label class="form-label" for="password_confirmation">Confirmar Contraseña</label>
    <div class="input-group input-group-merge">
      <input
        type="password"
        id="password_confirmation"
        name="password_confirmation"
        class="form-control"
        placeholder="&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;&#xb7;"
        @unless(isset($usuario)) required @endunless
        autocomplete="new-password"
      />
      <span class="input-group-text cursor-pointer"><i class="bx bx-hide"></i></span>
    </div>
  </div>

  <button type="submit" class="btn btn-primary">
    {{ isset($usuario) ? 'Actualizar Usuario' : 'Guardar Usuario' }}
  </button>
  <a href="{{ route('usuarios.index') }}" class="btn btn-secondary">Cancelar</a>
</form>
