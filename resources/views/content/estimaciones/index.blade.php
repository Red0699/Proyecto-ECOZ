@extends('layouts.contentNavbarLayout')
@section('title','Estimaciones COV')

{{-- Carga de librerías de terceros (CDN) --}}
@section('vendor-script')
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endsection

@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-estimaciones.scss',
])
@endsection

@section('page-script')
  {{-- ApexCharts (Materio suele ya incluirlo; si no, incluye el script correspondiente) --}}
  <script>
    (function () {
      const vols = @json(array_map(fn($r)=>$r['volumen'],$rows ?? []));
      const emis = @json(array_map(fn($r)=>$r['emision'],$rows ?? []));
      const cats = vols.map(v => (v/1000).toFixed(1) + 'k gal');

      if (document.getElementById('chart-vol-cov') && vols.length) {
        const options = {
          chart: { type: 'line', height: 220, toolbar: { show: false }, zoom: { enabled: false }, sparkline: { enabled: false }},
          stroke: { width: [3, 3], curve: 'smooth' },
          dataLabels: { enabled: false },
          series: [
            { name: 'Volumen (gal)', type: 'line', data: vols },
            { name: 'COV (kg/día)', type: 'line', data: emis }
          ],
          xaxis: { categories: cats, labels: { rotate: -15 } },
          yaxis: [
            { title: { text: 'Volumen (gal)' } },
            { opposite: true, title: { text: 'COV (kg/día)' } }
          ],
          grid: { strokeDashArray: 3 },
          markers: { size: 3 },
          legend: { position: 'top' },
          tooltip: {
            shared: true,
            intersect: false,
            y: [
              { formatter: val => new Intl.NumberFormat().format(val) + ' gal' },
              { formatter: val => (val ?? 0).toFixed(3) + ' kg/día' }
            ]
          }
        };
        const chart = new ApexCharts(document.querySelector('#chart-vol-cov'), options);
        chart.render();
      }
    })();
  </script>
@endsection

@section('content')
<div class="card est-cov-card shadow-sm">
  <div class="card-header d-flex align-items-center justify-content-between">
    <div class="est-title">
      <h5 class="mb-0">
        @if($estacion)
          Estimaciones COV — EDS {{ $estacion }}
        @else
          Estimaciones COV — Sin estación asignada
        @endif
      </h5>
      <span class="chip" data-bs-toggle="tooltip" title="Factor de emisión por 1000 galones">
        FE = {{ number_format($fe, 2) }} lb/1000 gal
      </span>
    </div>
  </div>

  <div class="card-body">
    <div class="formula-callout mb-3 mt-2">
      <code>Emisión COV (kg/día) = (Volumen/1000) × FE(lb/1000 gal) × 0.45359237</code>
      <small>Conversión 1 lb = 0.45359237 kg</small>
    </div>

    @if(!$estacion)
      <div class="alert alert-warning mb-0">
        No tienes una estación asignada. Solicita al administrador que te asigne una para visualizar esta sección.
      </div>
    @elseif(empty($rows))
      <div class="alert alert-info mb-0">
        No hay datos visuales definidos para la estación {{ $estacion }}.
      </div>
    @else

      {{-- KPIs --}}
      <div class="kpis">
        <div class="kpi">
          <div class="label">Filas</div>
          <div class="value">{{ number_format($stats['filas']) }}</div>
        </div>
        <div class="kpi">
          <div class="label">Volumen total (gal)</div>
          <div class="value text-mono">{{ number_format($stats['vol_total'], 0) }}</div>
        </div>
        <div class="kpi">
          <div class="label">COV total (kg/día)</div>
          <div class="value text-mono">{{ number_format($stats['cov_total'], 3) }}</div>
        </div>
        <div class="kpi">
          <div class="label">Rango volumen</div>
          <div class="value text-mono">{{ number_format($stats['vol_min'],0) }} – {{ number_format($stats['vol_max'],0) }}</div>
        </div>
      </div>

      {{-- Mini chart --}}
      <div class="apex-wrap">
        <div id="chart-vol-cov" style="height: 220px;"></div>
      </div>

      {{-- Tabla --}}
      <div class="table-responsive mt-3">
        <table class="table table-sm table-striped align-middle table-estimaciones">
          <thead>
            <tr>
              <th class="col-estacion">Estación</th>
              <th class="text-end col-volumen">Volumen (gal)</th>
              <th class="text-end col-emision">Emisión COV (kg/día)</th>
            </tr>
          </thead>
          <tbody>
            @foreach($rows as $r)
              <tr>
                <td>{{ $r['estacion'] }}</td>
                <td class="text-end text-mono">{{ number_format($r['volumen'], 0) }}</td>
                <td class="text-end text-mono"><strong>{{ number_format($r['emision'], 3) }}</strong></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <div class="legend-badges mt-2">
        @if($estacion === 'Silvania')
          <span class="badge bg-label-secondary">Tanque: 9.000 gal</span>
        @else
          <span class="badge bg-label-secondary">Tanques: 12.000 gal</span>
        @endif
        <span class="badge bg-label-secondary">Vista solo visual</span>
      </div>
    @endif
  </div>
</div>
@endsection
