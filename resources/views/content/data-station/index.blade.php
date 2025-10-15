@extends('layouts/contentNavbarLayout')

@section('title', 'Gestión de Datos')

@section('content')
<div class="container-fluid">

  {{-- Mensajes --}}
  @if(session('success'))
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    {{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif
  @if(session('error'))
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session('error') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
  @endif

  {{-- Cargar archivo --}}
  <div class="card mb-4 shadow-sm border-0">
    <div class="card-header bg-white border-bottom">
      <h5 class="mb-0">Carga y Visualización de Datos — Estación: <strong>{{ $stationName }}</strong></h5>
    </div>
    <div class="card-body">
      <form action="{{ route('datos.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
        @csrf
        <div class="col-md-6">
          <label for="excel_file" class="form-label fw-bold">Archivo Excel (.xlsx / .xls)</label>
          <input class="form-control" type="file" id="excel_file" name="excel_file" required>
          @error('excel_file')
          <div class="text-danger small mt-1">{{ $message }}</div>
          @enderror
        </div>

        <div class="col-md-3 d-flex align-items-end">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="preview_mode" name="preview_mode" value="1">
            <label class="form-check-label" for="preview_mode">Modo previsualización</label>
          </div>
        </div>

        <div class="col-md-3 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100">Procesar archivo</button>
        </div>
      </form>
    </div>
  </div>

  {{-- Filtros + Tabla --}}
  <div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Datos registrados</h5>
      <a href="{{ route('datos.lotes') }}" class="btn btn-sm btn-outline-secondary">Ver Lotes</a>
    </div>
    <div class="card-body">

      <form action="{{ route('datos.index') }}" method="GET" class="row g-3 mb-3">
        <div class="col-md-3">
          <label for="fecha" class="form-label">Fecha</label>
          <input type="date" id="fecha" name="fecha" class="form-control" value="{{ $fechaActual }}">
        </div>
        <div class="col-md-5">
          <label for="lote_id" class="form-label">Lote</label>
          <select id="lote_id" name="lote_id" class="form-select">
            <option value="">Todos</option>
            @foreach($lotes as $l)
            <option value="{{ $l->id }}" @selected($loteActual==$l->id)>
              #{{ $l->id }} · {{ $l->archivo }} · {{ $l->filas }} filas · {{ $l->estado }}
            </option>
            @endforeach
          </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
          <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
      </form>

      <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Fecha</th>
              <th>Hora</th>
              <th>Temp. Ambiente (°C)</th>
              <th>Humedad (%)</th>
              <th>Ventas (gl)</th>
              <th>Emisiones (kg)</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($data as $record)
            <tr>
              <td>{{ optional(\Carbon\Carbon::parse($record->fecha))->format('d/m/Y') }}</td>
              <td>{{ $record->hora }}</td>
              <td>{{ is_null($record->temperatura_ambiente_c) ? '—' : number_format($record->temperatura_ambiente_c, 1) }}</td>
              <td>
                @if(!is_null($record->humedad_ambiente))
                {{ number_format($record->humedad_ambiente * 100, 1) }}%
                @else
                —
                @endif
              </td>
              <td>{{ is_null($record->ventas_diarias_gl) ? '—' : number_format($record->ventas_diarias_gl, 2) }}</td>
              <td>{{ is_null($record->perdidas_totales_cov_kg) ? '—' : number_format($record->perdidas_totales_cov_kg, 3) }}</td>
            </tr>
            @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-4">No hay datos registrados.</td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <div class="mt-3">
        {{ $data->links() }}
      </div>
    </div>
  </div>

  {{-- MODAL DE PREVISUALIZACIÓN --}}
  @if(!empty($previewHeaders) && !empty($tempToken))
  <div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content shadow-lg border-0 rounded-3">
        <div class="modal-header bg-primary text-white rounded-top p-5">
          <h5 class="modal-title text-white" id="previewModalLabel">
            <i class="ti ti-eye me-2"></i> Previsualización de datos (no se han guardado)
          </h5>
          <button type="button"
            class="btn-close btn-close-white"
            id="btnCancelPreview"
            aria-label="Cancelar previsualización"></button>
        </div>

        <div class="modal-body p-0">
          {{-- Barra superior del modal (selector de filas por página) --}}
          <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
            <div class="small text-muted">
              Mostrando
              <strong>{{ count($previewRows) }}</strong>
              de <strong>{{ $previewMeta['total'] }}</strong> filas
            </div>
            <form method="GET" class="d-flex align-items-center gap-2">
              <input type="hidden" name="preview_page" value="1">
              <label for="pp" class="small mb-0">Filas por página</label>
              <select id="pp" name="preview_per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                @foreach([10,20,30,50] as $pp)
                <option value="{{ $pp }}" @selected($previewMeta['perPage']==$pp)>{{ $pp }}</option>
                @endforeach
              </select>
            </form>
          </div>

          <div class="table-responsive" style="max-height: 60vh;">
            <table class="table table-sm table-hover mb-0">
              <thead class="table-light sticky-top">
                <tr>
                  @foreach($previewHeaders as $h)
                  <th class="text-capitalize">{{ $h }}</th>
                  @endforeach
                </tr>
              </thead>
              <tbody>
                @foreach($previewRows as $r)
                <tr>
                  @foreach($previewHeaders as $h)
                  <td>{{ $r[$h] ?? '—' }}</td>
                  @endforeach
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer d-flex justify-content-between flex-wrap gap-2 bg-light rounded-bottom p-3">
          {{-- Paginación --}}
          @php
          $page = $previewMeta['page'];
          $last = $previewMeta['lastPage'];
          $perPage = $previewMeta['perPage'];
          @endphp
          <div class="btn-group" role="group" aria-label="Preview pager">
            <a class="btn btn-outline-secondary @if($page<=1) disabled @endif"
              href="{{ request()->fullUrlWithQuery(['preview_page' => max(1,$page-1), 'preview_per_page' => $perPage]) }}">
              ‹ Anterior
            </a>
            <span class="btn btn-outline-secondary disabled">Página {{ $page }} / {{ $last }}</span>
            <a class="btn btn-outline-secondary @if($page>=$last) disabled @endif"
              href="{{ request()->fullUrlWithQuery(['preview_page' => min($last,$page+1), 'preview_per_page' => $perPage]) }}">
              Siguiente ›
            </a>
          </div>

          <div class="d-flex gap-2">
            <form action="{{ route('datos.preview.cancel') }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-danger">
                <i class="ti ti-x me-1"></i> Cancelar
              </button>
            </form>
            <form action="{{ route('datos.preview.confirm') }}" method="POST">
              @csrf
              <button type="submit" class="btn btn-primary">
                <i class="ti ti-check me-1"></i> Confirmar e Importar
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
    @endif
  </div>
  @endsection

  @section('page-script')
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      @if(!empty($previewHeaders) && !empty($tempToken))
      var el = document.getElementById('previewModal');
      if (el) new bootstrap.Modal(el).show();
      @endif
    });
  </script>
  @endsection