@extends('layouts.contentNavbarLayout')
@section('title', 'Editar perfil')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-account-settings.scss'])
@endsection

@section('content')
<div class="card shadow-sm page-account-settings">
    <div class="profile-cover position-relative">
        <span class="profile-tag badge bg-label-primary position-absolute end-0 me-3 mt-3">Perfil</span>
    </div>

    <div class="card-body pt-0">

        <div class="d-flex align-items-end gap-3 profile-header">
            <div class="avatar-initials avatar-ring">
                {{ strtoupper(Str::of($user->name)->trim()->explode(' ')->map(fn($p)=>Str::substr($p,0,1))->take(2)->implode('')) }}
            </div>
            <div class="flex-grow-1">
                <h5 class="mb-1 fw-semibold lh-sm">{{ $user->name }}</h5>
                <div class="d-flex flex-wrap gap-2 align-items-center text-muted small">
                    <i class="ri-at-line"></i> <span>{{ $user->email }}</span>
                    <span class="vr mx-1 d-none d-sm-inline"></span>
                    <i class="ri-calendar-line"></i>
                    <span>Miembro desde {{ optional($user->created_at)->isoFormat('MMM YYYY') }}</span>
                </div>
            </div>
            {{-- botón contextual opcional --}}
            <div class="d-none d-md-block">
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">
                    <i class="ri-edit-2-line me-1"></i> Editar
                </a>
            </div>
        </div>

        {{-- Tabs “sticky” --}}
        <div class="tabs-sticky mt-6">
            <ul class="nav nav-pills" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-datos" role="tab">
                        <i class="ri-user-3-line me-1"></i> Datos Básicos
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-seguridad" role="tab">
                        <i class="ri-shield-keyhole-line me-1"></i> Seguridad
                    </button>
                </li>
            </ul>
        </div>

        {{-- Secciones con subtítulo y separación suave --}}
        <div class="tab-content mt-4">
            <div class="tab-pane fade show active" id="tab-datos">
                <div class="section-title">Información de usuario</div>
                <form method="POST" action="{{ route('profile.update') }}" class="row g-3 section-body">
                    @csrf @method('PUT')
                    {{-- ——— Inputs: Datos Básicos ——— --}}
                    @if ($errors->get('name') || $errors->get('email'))
                    <div class="col-12">
                        <div class="alert alert-danger mb-0">
                            <i class="ri-error-warning-line me-2"></i> Revisa los campos marcados en rojo.
                        </div>
                    </div>
                    @endif

                    <div class="col-md-6">
                        <label for="name" class="form-label">Nombre completo</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ri-user-line"></i></span>
                            <input
                                type="text"
                                id="name"
                                name="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}"
                                placeholder="Tu nombre y apellidos"
                                autocomplete="name">
                        </div>
                        @error('name')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ri-at-line"></i></span>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}"
                                placeholder="tucorreo@dominio.com"
                                autocomplete="email">
                        </div>
                        @error('email')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">
                            Usaremos este correo para notificaciones y recuperación de cuenta.
                        </small>
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-save-3-line me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
                @if(session('success'))
                <div class="alert alert-success mt-3 mb-0"><i class="ri-check-line me-2"></i>{{ session('success') }}</div>
                @endif
            </div>

            <div class="tab-pane fade" id="tab-seguridad">
                <div class="section-title">Cambiar contraseña</div>
                <form method="POST" action="{{ route('profile.password.update') }}" class="row g-3 section-body">
                    @csrf @method('PUT')
                    {{-- ——— Inputs: Seguridad ——— --}}
                    @if ($errors->get('current_password') || $errors->get('password') || $errors->get('password_confirmation'))
                    <div class="col-12">
                        <div class="alert alert-danger mb-0">
                            <i class="ri-error-warning-line me-2"></i> Corrige los errores antes de continuar.
                        </div>
                    </div>
                    @endif

                    <div class="col-md-4">
                        <label for="current_password" class="form-label">Contraseña actual</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ri-lock-2-line"></i></span>
                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                class="form-control @error('current_password') is-invalid @enderror"
                                placeholder="••••••••"
                                autocomplete="current-password">
                            <span class="input-group-text cursor-pointer toggle-pass" title="Mostrar/Ocultar"><i class="ri-eye-line"></i></span>
                        </div>
                        @error('current_password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label for="password" class="form-label">Nueva contraseña</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ri-key-2-line"></i></span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control @error('password') is-invalid @enderror"
                                placeholder="Mínimo 8 caracteres"
                                autocomplete="new-password">
                            <span class="input-group-text cursor-pointer toggle-pass" title="Mostrar/Ocultar"><i class="ri-eye-line"></i></span>
                        </div>
                        @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">Usa una combinación de letras, números y símbolos.</small>
                    </div>

                    <div class="col-md-4">
                        <label for="password_confirmation" class="form-label">Confirmar nueva contraseña</label>
                        <div class="input-group input-group-merge">
                            <span class="input-group-text"><i class="ri-key-line"></i></span>
                            <input
                                type="password"
                                id="password_confirmation"
                                name="password_confirmation"
                                class="form-control"
                                placeholder="Repite la nueva contraseña"
                                autocomplete="new-password">
                            <span class="input-group-text cursor-pointer toggle-pass" title="Mostrar/Ocultar"><i class="ri-eye-line"></i></span>
                        </div>
                    </div>

                    <div class="col-12 d-flex justify-content-end">
                        <button type="submit" class="btn btn-dark">
                            <i class="ri-lock-password-line me-1"></i> Actualizar contraseña
                        </button>
                    </div>
                </form>
                @if(session('success_password'))
                <div class="alert alert-success mt-3 mb-0"><i class="ri-check-line me-2"></i>{{ session('success_password') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-script')
<script>
    // Mostrar/ocultar contraseñas
    document.querySelectorAll('.toggle-pass').forEach(btn => {
        btn.addEventListener('click', () => {
            const input = btn.parentElement.querySelector('input');
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });
</script>
@endsection