@extends('layouts/contentNavbarLayout')
@section('title','Registro histórico')

@section('vendor-script')
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endsection

@section('page-script')
  <script>
    // Inyectamos datos ANTES del bundle
    window.series = {!! json_encode($series, JSON_UNESCAPED_UNICODE) !!};
    window.kpis   = {!! json_encode($kpis,   JSON_UNESCAPED_UNICODE) !!};
  </script>
  @vite('resources/assets/js/historical-records-charts.js')
@endsection

@section('content')
<div class="container-xxl py-3">
  <div class="d-flex align-items-center justify-content-between mb-2">
    <h4 class="mb-0">Registro histórico — {{ $estacionNombre }}</h4>
  </div>

  {{-- Filtros (sin select de estación) --}}
  <form class="row g-2 mb-3" method="GET" action="{{ route('registro-historico') }}">
    <div class="col-6 col-md-3">
      <label class="form-label">Desde</label>
      <input type="date" name="from" value="{{ $from }}" class="form-control">
    </div>
    <div class="col-6 col-md-3">
      <label class="form-label">Hasta</label>
      <input type="date" name="to" value="{{ $to }}" class="form-control">
    </div>
    <div class="col-12 col-md-2 d-flex align-items-end">
      <button class="btn btn-primary w-100">Aplicar</button>
    </div>
  </form>

  {{-- INVENTARIO: Volumen (gl) --}}
  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex justify-content-between">
        <h6 class="mb-2">Inventario (Volumen, gl)</h6>
        <small class="text-muted">{{ $kpis['inventario_ultimo_str'] ?? '—' }}</small>
      </div>
      <div id="invChart"></div>
    </div>
  </div>

  {{-- PRESIÓN + PÉRDIDAS (COV) --}}
  <div class="row g-3 mb-3">
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="mb-2">Presión (Psi)</h6>
          <div id="presionChart"></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="mb-2">Pérdidas totales de emisión de vapor (COV, kg/día)</h6>
          <div id="perdidasChart"></div>
        </div>
      </div>
    </div>
  </div>

  {{-- GRAFICA DIARIA (ventas/descargue - opcional) --}}
  <div class="card mb-3">
    <div class="card-body">
      <h6 class="mb-2">Gráfica diaria (Ventas / Descargue)</h6>
      <div id="graficaDiariaChart"></div>
    </div>
  </div>

  {{-- CÁLCULO COV / CO2 + GRÁFICA COV --}}
  <div class="row g-3 mb-3">
    <div class="col-12 col-lg-4">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="mb-2">Cálculo COV y CO₂</h6>
          <ul class="mb-0">
            <li><strong>COV total:</strong> {{ number_format($kpis['cov_total_kg'] ?? 0, 3) }} kg</li>
            <li><strong>CO₂e total:</strong> {{ number_format($kpis['co2_total_kg'] ?? 0, 3) }} kg</li>
            <li><strong>Factor (CO₂/COV):</strong>
              @if(!empty($kpis['factor_cov_to_co2']))
                {{ number_format($kpis['factor_cov_to_co2'], 4) }} kg/kg
              @else — @endif
            </li>
          </ul>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-8">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="mb-2">Gráfica COV (kg/día)</h6>
          <div id="covChart"></div>
        </div>
      </div>
    </div>
  </div>

  {{-- VARIACIÓN (faltante/sobrante) --}}
  <div class="card mb-3">
    <div class="card-body">
      <h6 class="mb-2">Sumatoria valor faltante o sobrante diario (gl)</h6>
      <div id="variacionChart"></div>
    </div>
  </div>

  {{-- NOTIFICACIONES / PARÁMETROS (placeholder) --}}
  <div class="card">
    <div class="card-body">
      <h6 class="mb-2">Notificaciones, parámetros legales y normativos</h6>
      @if(empty($alerts))
        <p class="text-muted mb-0">Sin alertas en el rango.</p>
      @else
        <ul class="mb-0">
          @foreach($alerts as $a)
            <li>{{ $a }}</li>
          @endforeach
        </ul>
      @endif
    </div>
  </div>
</div>
@endsection
