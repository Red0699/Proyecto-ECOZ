@extends('layouts/contentNavbarLayout')

@section('title', 'Registro Histórico - Cota')

{{-- Carga los estilos de ApexCharts --}}
@section('vendor-style')
@vite('resources/assets/vendor/libs/apex-charts/apex-charts.scss')
@endsection

{{-- Carga la librería de ApexCharts --}}
@section('vendor-script')
@vite('resources/assets/vendor/libs/apex-charts/apexcharts.js')
@endsection

{{-- Carga nuestro archivo JS y le pasa los datos --}}
@section('page-script')
<script>
  if (typeof isDarkStyle === 'undefined') {
    var isDarkStyle = false;
  }
  const kpis = {
    !!json_encode($kpis) !!
  };
  const chartData = {
    !!json_encode($chartData) !!
  };
</script>
@vite('resources/assets/js/historical-records-charts.js')
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold">
    <span class="text-muted fw-light">Estación {{ $stationName }} /</span> Registro Histórico
  </h4>
  {{-- Filtro de Fecha --}}
  <form action="{{ route('registro-historico') }}" method="GET" class="d-flex align-items-center">
    <label for="date" class="form-label me-2 mb-0">Fecha:</label>
    <input type="date" id="date" name="date" class="form-control"
      value="{{ \Carbon\Carbon::parse($targetDate)->format('Y-m-d') }}"
      onchange="this.form.submit()">
  </form>
</div>

<div class="row gy-4 mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card card-border-shadow-primary h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2"><span class="avatar-initial rounded bg-label-primary"><i class="ri-thermometer-line"></i></span></div>
          <h4 class="ms-1 mb-0">{{ number_format($kpis['avg_temp'] ?? 0, 1) }} °C</h4>
        </div>
        <p class="mb-1">Temperatura Promedio</p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card card-border-shadow-info h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2"><span class="avatar-initial rounded bg-label-info"><i class="ri-water-percent-line"></i></span></div>
          <h4 class="ms-1 mb-0">{{ number_format(($kpis['avg_humidity'] ?? 0) * 100, 1) }}%</h4>
        </div>
        <p class="mb-1">Humedad Promedio</p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card card-border-shadow-warning h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2"><span class="avatar-initial rounded bg-label-warning"><i class="ri-gas-station-line"></i></span></div>
          <h4 class="ms-1 mb-0">{{ number_format($kpis['daily_sales'] ?? 0, 2) }} gl</h4>
        </div>
        <p class="mb-1">Ventas Diarias</p>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card card-border-shadow-danger h-100">
      <div class="card-body">
        <div class="d-flex align-items-center mb-2 pb-1">
          <div class="avatar me-2"><span class="avatar-initial rounded bg-label-danger"><i class="ri-cloud-windy-line"></i></span></div>
          <h4 class="ms-1 mb-0">{{ number_format($kpis['total_emissions'] ?? 0, 4) }} kg</h4>
        </div>
        <p class="mb-1">Emisiones Totales</p>
      </div>
    </div>
  </div>
</div>

<div class="row gy-4 mb-4">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Temperatura vs Humedad</h5>
      </div>
      <div class="card-body">
        <div id="tempHumidityChart"></div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Emisiones vs Ventas</h5>
      </div>
      <div class="card-body">
        <div id="emissionsSalesChart"></div>
      </div>
    </div>
  </div>
</div>

<div class="row gy-4">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Presiones de Vapor (MmHg)</h5>
      </div>
      <div class="card-body">
        <div id="pressuresChart"></div>
      </div>
    </div>
  </div>
</div>
@endsection