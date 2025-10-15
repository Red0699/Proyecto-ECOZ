@extends('layouts/contentNavbarLayout')

@section('title', 'Lotes de importación')

@section('content')
<div class="container-fluid">

  @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif
  @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  @endif

  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold">Lotes de importación</h4>
    <a href="{{ route('datos.index') }}" class="btn btn-sm btn-outline-secondary">Volver a Datos</a>
  </div>

  <div class="card shadow-sm border-0">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>ID</th>
              <th>Archivo</th>
              <th>Filas</th>
              <th>Estado</th>
              <th>Fecha</th>
              <th class="text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            @forelse($lotes as $lote)
            <tr>
              <td>#{{ $lote->id }}</td>
              <td>{{ $lote->archivo }}</td>
              <td>{{ $lote->filas }}</td>
              <td>
                <span class="badge bg-{{ $lote->estado==='ok'?'success':($lote->estado==='failed'?'danger':'warning') }}">
                  {{ strtoupper($lote->estado) }}
                </span>
              </td>
              <td>{{ $lote->created_at?->format('d/m/Y H:i') }}</td>
              <td class="text-center">
                <div class="btn-group">
                  <a href="{{ route('datos.index', ['lote_id'=>$lote->id]) }}" class="btn btn-sm btn-primary">Ver datos</a>
                  <form action="{{ route('datos.lotes.destroy', $lote->id) }}" method="POST" onsubmit="return confirm('¿Eliminar el lote #{{ $lote->id }} y todos sus datos?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                  </form>
                </div>
              </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center text-muted py-4">No hay lotes registrados.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer">
      {{ $lotes->links() }}
    </div>
  </div>
</div>
@endsection
