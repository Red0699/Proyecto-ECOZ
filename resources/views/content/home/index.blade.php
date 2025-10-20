@extends('layouts/contentNavbarLayout')
@section('title','Inicio')

@section('vendor-script')
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endsection

@section('page-script')
  {{-- Inyecta los datos ANTES de cargar el JS de gráficos --}}
  <script id="home-bootstrap">
    window.homeSeries = @json($series, JSON_UNESCAPED_UNICODE);
    window.homeKpis   = @json($kpis,   JSON_UNESCAPED_UNICODE);
  </script>

  @vite('resources/assets/js/home-charts.js')
@endsection

@section('content')
<div class="container-xxl py-2">
  <div class="d-flex align-items-center justify-content-between mb-2">
    <h6 class="mb-0">Inicio — {{ $estacionNombre }}</h6>
    <small class="text-muted">Último día: {{ $kpis['last_day_str'] ?? '—' }} @if(!empty($kpis['last_lote'])) · Lote {{ $kpis['last_lote'] }} @endif</small>
  </div>

  {{-- F1: Tanque + COV (todos los días) --}}
  <div class="row g-2 mb-2">
  {{-- Inventario --}}
  <div class="col-12 col-lg-4">
    <div class="card h-100">
      <div class="card-body">
        {{-- Encabezado --}}
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0 fw-semibold">Inventario</h6>
          <small class="text-muted">
            {{ $kpis['last_day_str'] ?? '—' }}
          </small>
        </div>

        {{-- Contenido gráfico --}}
        <div class="d-flex align-items-center justify-content-center">
          <div id="tankWidget" class="tank"></div>
          <div class="ms-2">
            <div class="fs-5 fw-semibold text-body">
              {{ number_format($kpis['inv_ult_gl'] ?? 0, 2) }} <span class="text-muted">gl</span>
            </div>
            <div class="text-muted small">Capacidad</div>
            <div class="fs-6 text-body-secondary">
              {{ number_format($kpis['inv_capacity_gl'] ?? 0, 0) }} gl
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- COV (kg/día) --}}
  <div class="col-12 col-lg-8">
    <div class="card h-100">
      <div class="card-body">
        <h6 class="fw-semibold">Emisiones COV mensual</h6>
        <div id="covAllDaysChart" class="chart-190"></div>
      </div>
    </div>
  </div>
</div>


  {{-- F2: Presión (todos los días) + KPIs último día --}}
  <div class="row g-2 mb-2">
    <div class="col-12 col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <h6>Presión promedio mensual</h6>
          <div id="presionAvgDaysChart" class="chart-170"></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
  <div class="card h-100 shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0 fw-semibold">Estimaciones</h6>
        <span class="badge bg-light text-body-secondary border">
          {{ $kpis['last_day_str'] ?? '—' }}
        </span>
      </div>

      <div class="row g-2">
        {{-- COV --}}
        <div class="col-6">
          <div class="h-100 p-2 ps-3 bg-light rounded border border-0 border-start border-4 border-danger-subtle">
            <small class="text-body-secondary d-block">COV</small>
            <div class="h5 mb-0 fw-bold text-danger">
              {{ number_format($kpis['last_day_cov_kg'] ?? 0, 3) }}
              <span class="fs-6 fw-semibold text-body-secondary">kg</span>
            </div>
          </div>
        </div>

        {{-- CO2 --}}
        <div class="col-6">
          <div class="h-100 p-2 ps-3 bg-light rounded border border-0 border-start border-4 border-primary-subtle">
            <small class="text-body-secondary d-block">CO<sub>2</sub></small>
            <div class="h5 mb-0 fw-bold text-primary">
              {{ number_format($kpis['last_day_co2_kg'] ?? 0, 3) }}
              <span class="fs-6 fw-semibold text-body-secondary">kg</span>
            </div>
          </div>
        </div>
      </div>

      <hr class="my-2">

      {{-- Pérdida por respiración y trabajo en el tanque --}}
      <div class="p-2 ps-3 bg-light rounded border border-0 border-start border-4 border-success-subtle">
        <small class="text-body-secondary d-block">Pérdida por respiración y trabajo en el tanque</small>
        <div class="h5 mb-0 fw-bold text-success">
          {{ number_format($kpis['last_day_variacion_gl'] ?? 0, 2) }}
          <span class="fs-6 fw-semibold text-body-secondary">gl</span>
        </div>
      </div>
    </div>
  </div>
</div>

  </div>

{{-- F2.5: Gráfica diaria (último día) --}}
<div class="row g-2 mb-2">
  <div class="col-12">
    <div class="card h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <h6 class="mb-0 fw-semibold">Comparación de la distribución de pérdidas totales</h6>
          <small class="text-muted">{{ $kpis['last_day_str'] ?? '—' }}</small>
        </div>
        <div id="homeGraficaDiaria" class="chart-190"></div>
      </div>
    </div>
  </div>
</div>

  
  {{-- F3: Notificaciones --}}
  <div class="row g-2">
    <div class="col-12">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="mb-2">Notificaciones, parámetros legales y normativos</h6>
          @if(empty($alerts)) <p class="text-muted mb-0">Sin alertas.</p>
          @else <ul class="mb-0">@foreach($alerts as $a)<li>{{ $a }}</li>@endforeach</ul>@endif
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
