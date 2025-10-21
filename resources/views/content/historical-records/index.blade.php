{{-- resources/views/content/historical-records/index.blade.php --}}
@extends('layouts/contentNavbarLayout')
@section('title','Registro histórico')

@section('vendor-script')
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endsection

@section('page-style')
  @vite('resources/assets/vendor/scss/pages/historical-records-charts.scss')
@endsection

@section('page-script')
  <script>
    window.series = {!! json_encode($series, JSON_UNESCAPED_UNICODE) !!};
    window.kpis   = {!! json_encode($kpis,   JSON_UNESCAPED_UNICODE) !!};
    window.filterMeta = {!! json_encode(['mode'=>$mode,'year'=>$year,'month'=>$month,'from'=>$from,'to'=>$to]) !!};
  </script>
  @vite('resources/assets/js/historical-records-charts.js')
@endsection

@section('content')
<div class="container-xxl py-3 hrx">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h4 class="mb-1">Registro histórico — {{ $estacionNombre }}</h4>
      <div class="text-muted small">
        @if($mode==='year') Año {{ $year }}
        @elseif($mode==='month') {{ \Carbon\Carbon::createFromDate($year,$month,1)->isoFormat('MMMM YYYY') }}
        @else Rango: {{ $from }} → {{ $to }}
        @endif
      </div>
    </div>
    <a href="{{ route('registro-historico.pdf', request()->all()) }}" class="btn btn-outline-secondary">
      <i class="ri-file-pdf-2-line me-1"></i> PDF
    </a>
  </div>

  {{-- Filtros profesionales --}}
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <form class="row g-3 align-items-end" method="GET" action="{{ route('registro-historico') }}">
        <div class="col-12">
          <div class="nav nav-pills hrx-pills mb-2" role="tablist">
            @php $m = $mode; @endphp
            <button class="btn btn-sm {{ $m==='custom'?'btn-primary':'btn-outline-primary' }}" type="submit" name="mode" value="custom">Personalizado</button>
            <button class="btn btn-sm {{ $m==='month'?'btn-primary':'btn-outline-primary' }} ms-2" type="submit" name="mode" value="month">Mensual</button>
            <button class="btn btn-sm {{ $m==='year'?'btn-primary':'btn-outline-primary' }} ms-2" type="submit" name="mode" value="year">Anual</button>
          </div>
        </div>

        {{-- Personalizado --}}
        <div class="col-6 col-md-3">
          <label class="form-label">Desde</label>
          <input type="date" name="from" value="{{ $from }}" class="form-control" {{ $mode!=='custom'?'disabled':'' }}>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Hasta</label>
          <input type="date" name="to" value="{{ $to }}" class="form-control" {{ $mode!=='custom'?'disabled':'' }}>
        </div>

        {{-- Mensual --}}
        <div class="col-6 col-md-3">
          <label class="form-label">Mes</label>
          <select name="month" class="form-select" {{ $mode!=='month'?'disabled':'' }}>
            @for($i=1;$i<=12;$i++)
              <option value="{{ $i }}" {{ (int)$month===$i?'selected':'' }}>{{ \Carbon\Carbon::createFromDate(2000,$i,1)->isoFormat('MMMM') }}</option>
            @endfor
          </select>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label">Año</label>
          <input type="number" name="year" value="{{ $year }}" class="form-control" {{ $mode==='custom'?'':' ' }}>
        </div>

        <div class="col-12 col-md-2 ms-auto">
          <button class="btn btn-primary w-100"><i class="ri-filter-2-line me-1"></i>Aplicar</button>
        </div>
      </form>
    </div>
  </div>

  {{-- KPIs resumidos --}}
  <div class="row g-3 mb-3">
    <div class="col-12 col-md-4">
      <div class="hrx-kpi card">
        <div class="card-body">
          <div class="hrx-kpi-label">COV total</div>
          <div class="hrx-kpi-value">{{ number_format($kpis['cov_total_kg'] ?? 0, 3) }} <span>kg</span></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="hrx-kpi card">
        <div class="card-body">
          <div class="hrx-kpi-label">CO₂e total</div>
          <div class="hrx-kpi-value">{{ number_format($kpis['co2_total_kg'] ?? 0, 3) }} <span>kg</span></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="hrx-kpi card">
        <div class="card-body">
          <div class="hrx-kpi-label">Factor (CO₂/COV)</div>
          <div class="hrx-kpi-value">
            @if(!empty($kpis['factor_cov_to_co2'])) {{ number_format($kpis['factor_cov_to_co2'], 4) }} <span>kg</span>
            @else — @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Gráficas principales (Inventario, Presión, COV) --}}
  <div class="card mb-3 shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="mb-0">Inventario</h6>
        <small class="text-muted">{{ $kpis['inventario_ultimo_str'] ?? '—' }}</small>
      </div>
      <div id="invChart"></div>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h6 class="mb-2">Presión (Psi)</h6>
          <div id="presionChart"></div>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card h-100 shadow-sm">
        <div class="card-body">
          <h6 class="mb-2">COV (kg)</h6>
        <div id="covChart"></div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
