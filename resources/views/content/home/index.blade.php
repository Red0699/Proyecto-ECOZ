@extends('layouts/contentNavbarLayout')
@section('title','Inicio')

@section('vendor-script')
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endsection

@section('page-style')
@vite([
'resources/assets/vendor/scss/pages/page-home.scss'
])
@endsection

@section('page-script')
  {{-- Inyecta los datos ANTES del JS --}}
  <script id="home-bootstrap">
    window.homeSeries = @json($series, JSON_UNESCAPED_UNICODE);
    window.homeKpis   = @json($kpis,   JSON_UNESCAPED_UNICODE);
    window.homeAlerts = @json($alerts, JSON_UNESCAPED_UNICODE); // puede traer objetos o strings
  </script>

  <script>
    // -------- Helpers ----------
    function normalizeAlert(a) {
      // Acepta string u objeto {severity, norma, mensaje}
      if (a && typeof a === 'object') {
        return {
          severity: a.severity || 'warning',
          norma:    a.norma    || 'Alerta',
          mensaje:  a.mensaje  || ''
        };
      }
      return { severity: 'warning', norma: 'Alerta', mensaje: String(a ?? '') };
    }

    function pickOneAlert(alerts) {
      // Muestra 1 sola: prioriza danger > warning > info
      const arr = alerts.map(normalizeAlert);
      return (
        arr.find(x => x.severity === 'danger')  ||
        arr.find(x => x.severity === 'warning') ||
        arr.find(x => x.severity === 'info')    ||
        null
      );
    }

    function toastHtml(alert) {
      const sev = alert.severity;
      const color = (sev === 'danger' ? 'danger' : sev === 'warning' ? 'warning' : 'info');
      const title = alert.norma || 'Alerta';
      const msg = alert.mensaje || '';
      return `
        <div class="toast align-items-center text-bg-${color}" role="alert" aria-live="assertive" aria-atomic="true"
             style="position: fixed; top: 16px; right: 16px; z-index: 1080;">
          <div class="d-flex">
            <div class="toast-body">
              <strong>${title}:</strong> ${msg}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
          </div>
        </div>`;
    }

    function showOneToastPerDay(alert) {
      const today = new Date().toISOString().slice(0,10); // YYYY-MM-DD
      // Clave por norma + día (evita repetir el mismo aviso en el día)
      const key = `ecoz:toast:${(alert.norma || 'alert').toLowerCase()}:${today}`;

      if (!localStorage.getItem(key)) {
        const wrapper = document.createElement('div');
        wrapper.innerHTML = toastHtml(alert);
        const el = wrapper.firstElementChild;
        document.body.appendChild(el);
        const t = new bootstrap.Toast(el, { delay: 6000 });
        t.show();
        localStorage.setItem(key, '1');
      }
    }

    document.addEventListener('DOMContentLoaded', function () {
      const alerts = Array.isArray(window.homeAlerts) ? window.homeAlerts : [];
      const one = pickOneAlert(alerts);
      if (one) showOneToastPerDay(one);
      
    });
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
      <div class="card notify-card h-100 shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between py-2">
          <div class="d-flex align-items-center gap-2">
            {{-- Ícono inline (no dependencias) --}}
            <svg class="notify-icon-title" width="18" height="18" viewBox="0 0 24 24" fill="none">
              <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2Zm6-6v-5a6 6 0 0 0-12 0v5l-2 2v1h16v-1l-2-2Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <h6 class="mb-0 fw-semibold">Notificaciones, parámetros legales y normativos</h6>
          </div>

          <span class="badge notify-badge-count">
            {{ empty($alerts) ? '0' : count($alerts) }}
          </span>
        </div>

        <div class="card-body py-2">
          @if(empty($alerts))
            <div class="notify-empty">
              <div class="notify-empty__icon">😊</div>
              <div class="notify-empty__title">Sin alertas</div>
              <div class="notify-empty__text text-muted">Todo en orden por ahora.</div>
            </div>
          @else
            <ul class="notify-list">
              @foreach($alerts as $a)
                @php
                  // Soporta strings o arrays estructurados {severity, norma, mensaje}
                  $isObj   = is_array($a);
                  $sev     = $isObj ? ($a['severity'] ?? 'warning') : 'warning';
                  $title   = $isObj ? ($a['norma'] ?? 'Alerta normativa') : 'Alerta normativa';
                  $message = $isObj ? ($a['mensaje'] ?? (string)$a) : (string)$a;
                @endphp
                <li class="notify-item notify-item--{{ $sev }}">
                  <div class="notify-item__icon">
                    @if($sev === 'danger')
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                      </svg>
                    @elseif($sev === 'warning')
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M12 8v5m0 4h.01M4.93 19h14.14c1.54 0 2.5-1.67 1.73-3L13.73 4a2 2 0 0 0-3.46 0L3.2 16c-.77 1.33.19 3 1.73 3Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                      </svg>
                    @else
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                        <path d="M13 16h-1V8h-1m2 8a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM12 3a9 9 0 1 1 0 18 9 9 0 0 1 0-18Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
                      </svg>
                    @endif
                  </div>
                  <div class="notify-item__content">
                    <div class="notify-item__title">{{ $title }}</div>
                    <div class="notify-item__text">{{ $message }}</div>
                  </div>
                  <div class="notify-item__tag">
                    <span class="chip chip-{{ $sev }}">
                      {{ $sev === 'danger' ? 'Crítica' : ($sev === 'warning' ? 'Atención' : 'Info') }}
                    </span>
                  </div>
                </li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
