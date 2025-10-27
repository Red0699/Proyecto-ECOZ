@extends('layouts/contentNavbarLayout')

@section('title', 'Gestión de Usuarios')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/users-index.scss'])
@endsection

@section('content')
<h4 class="fw-bold py-3 mb-4">
    <span class="text-muted fw-light">Administración /</span> Usuarios
</h4>

<div class="card users-index">
    <div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <h5 class="mb-0">Usuarios Registrados</h5>
            <span class="badge bg-label-secondary">{{ $users->total() }} total</span>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <form method="GET" class="d-flex align-items-center gap-2">
                <div class="input-group input-group-merge">
                    <span class="input-group-text"><i class="bx bx-search"></i></span>
                    <input
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        class="form-control"
                        placeholder="Buscar por nombre, correo o estación">
                    @if(request()->has('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                    @endif
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ (int)request('per_page', $users->perPage()) }}/página
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @foreach([10,25,50] as $n)
                        <li>
                            <a class="dropdown-item @if((int)request('per_page', $users->perPage())===$n) active @endif"
                                href="{{ request()->fullUrlWithQuery(['per_page'=>$n]) }}">
                                {{ $n }}/página
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </form>

            <a href="{{ route('usuarios.create') }}" class="btn btn-primary d-inline-flex align-items-center">
                <i class="bx bx-user-plus me-1"></i>
                <span>Añadir Usuario</span>
            </a>
        </div>
    </div>


    @if($users->count() === 0)
    <div class="card-body text-center py-5">
        <img src="{{ asset('assets/img/illustrations/empty-state.png') }}" alt="" class="empty-illustration mb-3">
        <h6 class="mb-1">Aún no hay usuarios</h6>
        <p class="text-muted mb-3">Crea tu primer usuario para comenzar la administración.</p>
        <a href="{{ route('usuarios.create') }}" class="btn btn-primary">Crear usuario</a>
    </div>
    @else
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="w-60px">ID</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Estación</th>
                    <th>Creado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach ($users as $user)
                <tr>
                    <td class="text-muted">#{{ $user->id }}</td>

                    {{-- Usuario (avatar + nombre + email) --}}
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-initials avatar-ring" title="{{ $user->name }}">
                                {{ \Illuminate\Support\Str::of($user->name)->trim()->explode(' ')->map(fn($p)=>\Illuminate\Support\Str::substr($p,0,1))->take(2)->implode('') }}
                            </div>
                            <div>
                                <div class="fw-medium lh-sm">{{ $user->name }}</div>
                                <div class="small text-muted">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- Rol (badge) --}}
                    <td>
                        @php $roleName = $user->role->display_name ?? $user->role->name ?? 'Sin rol'; @endphp
                        <span class="badge role-badge" data-role="{{ \Illuminate\Support\Str::slug($roleName) }}">{{ $roleName }}</span>
                    </td>

                    {{-- Estación (chip) --}}
                    <td>
                        @if($user->estacion?->nombre)
                        <span class="chip-estacion" title="ID {{ $user->estacion->id }}">
                            <i class="ri-gas-station-line me-1"></i> {{ $user->estacion->nombre }}
                        </span>
                        @else
                        <span class="text-muted">— Sin asignar —</span>
                        @endif
                    </td>

                    {{-- Fecha de creación --}}
                    <td>
                        <span class="small text-muted" title="{{ $user->created_at->toDayDateTimeString() }}">
                            {{ $user->created_at->format('d/m/Y H:i') }}
                        </span>
                    </td>

                    {{-- Acciones --}}
                    {{-- Acciones --}}
                    @php
                    $auth = auth()->user();
                    $isAdmin = str_contains(strtolower($auth->role?->name ?? $auth->role?->display_name ?? ''), 'admin');
                    $canEdit = $isAdmin || $auth->id === $user->id; // Admin o el propio usuario
                    $canDelete = $isAdmin && $auth->id !== $user->id; // Solo admin y no a sí mismo
                    @endphp

                    <td class="text-end">
                        <div class="d-inline-flex gap-1">
                            @if($canEdit)
                            <a href="{{ route('usuarios.edit', $user) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-edit-alt me-1"></i> Editar
                            </a>
                            @endif

                            @if($canDelete)
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#modalDeleteUser"
                                data-user-id="{{ $user->id }}"
                                data-user-name="{{ $user->name }}"
                                data-action="{{ route('usuarios.destroy', $user) }}">
                                <i class="bx bx-trash me-1"></i> Eliminar
                            </button>
                            @endif
                        </div>
                    </td>


                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
        <small class="text-muted">
            Mostrando {{ $users->firstItem() }}–{{ $users->lastItem() }} de {{ $users->total() }}
        </small>
        {{ $users->withQueryString()->links() }}
    </div>
    @endif
</div>

{{-- Modal eliminar --}}
<div class="modal fade" id="modalDeleteUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" class="modal-content" id="deleteUserForm">
            @csrf @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title">Eliminar usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">¿Seguro que deseas eliminar al usuario <strong id="deleteUserName">—</strong>? Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('modalDeleteUser');
        if (!modal) return;
        modal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            const userName = btn?.getAttribute('data-user-name') || '—';
            const action = btn?.getAttribute('data-action') || '#';

            modal.querySelector('#deleteUserName').textContent = userName;
            const form = document.getElementById('deleteUserForm');
            form.setAttribute('action', action);
        });
    });
</script>
@endsection