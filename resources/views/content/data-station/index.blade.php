@extends('layouts/contentNavbarLayout')

@section('title', 'Gestión de Datos')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Herramientas /</span> Carga y Visualización de Datos
</h4>

<div class="card mb-4">
  <div class="card-header">
    <h5 class="mb-0">Cargar Nuevo Archivo de Datos para la Estación: <strong>{{ $stationName }}</strong></h5>
  </div>
  <div class="card-body">
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('datos.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="mb-3">
        <label for="excel_file" class="form-label">Selecciona el archivo Excel del día</label>
        <input class="form-control" type="file" id="excel_file" name="excel_file" required>
        @error('excel_file')
        <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
      </div>
      <button type="submit" class="btn btn-primary">Cargar y Registrar Datos</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <h5 class="mb-0">Datos Registrados para {{ $stationName }}</h5>
  </div>
  <div class="card-body">
    <form action="{{ route('datos.index') }}" method="GET" class="row g-3 mb-4">
      <div class="col-md-4">
        <label for="fecha" class="form-label">Filtrar por Fecha</label>
        <input type="date" id="fecha" name="fecha" class="form-control" value="{{ request('fecha') }}">
      </div>
      <div class="col-md-4 d-flex align-items-end">
        <button type="submit" class="btn btn-primary">Buscar</button>
      </div>
    </form>
    <div class="table-responsive text-nowrap">
      <table class="table" id="dataTable">
        <thead>
          <tr>
            <th>Fecha</th>
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
            <td colspan="5" class="text-center">No hay datos registrados.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="card-footer">
      {{ $data->appends(request()->query())->links() }}
    </div>
  </div>
</div>
@endsection