@extends('layouts/contentNavbarLayout')
@section('title','Normativas')

@section('page-style')
@vite('resources/assets/vendor/scss/pages/page-normativas.scss')
@endsection

@section('content')
@php
$user = Auth::user();
$miEstacionId = $user?->estacion_id;
$miEstacionNombre = $user?->estacion?->nombre ?? '—';
@endphp

<div class="container-xxl py-3 norma-page">
  <!-- Encabezado -->
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div class="d-flex align-items-center gap-2">
      <svg width="22" height="22" viewBox="0 0 24 24" class="text-primary">
        <path d="M6 4h12a2 2 0 0 1 2 2v11.5a.5.5 0 0 1-.76.43L12 14l-7.24 3.93A.5.5 0 0 1 4 17.5V6a2 2 0 0 1 2-2Z" fill="currentColor" />
      </svg>
      <div>
        <h5 class="mb-0 fw-semibold">Normativas aplicables</h5>
        <small class="text-muted">Generales y específicas por estación</small>
      </div>
    </div>
    <span class="badge bg-label-primary border">
      Estación: <strong class="ms-1">{{ $miEstacionNombre }}</strong>
    </span>
  </div>

  @if($normativas->isEmpty())
  <div class="alert alert-info">No hay normativas registradas.</div>
  @else
  <div id="normaGrid" class="row g-3">
@foreach($normativas as $n)
  @php
    $scope = 'general';
    if (!is_null($n->estacion_id)) {
      $scope = ($n->estacion_id === $miEstacionId) ? 'mine' : 'other';
    }

    // Normaliza sanciones: string o array
    $sanRaw  = $n->sanciones ?? null;
    $sanList = is_array($sanRaw)
      ? $sanRaw
      : (filled($sanRaw) ? [ ['titulo'=>'Sanciones','detalle'=>$sanRaw,'nivel'=>'alta'] ] : []);
    $hasSan  = count($sanList) > 0;
    $collapseId = 'san-'.$n->id;
  @endphp

  <div class="col-12 col-lg-6 norma-item" data-scope="{{ $scope }}">
    <div class="card norma-card h-100 shadow-sm">
      {{-- SIEMPRE informativa (azul) --}}
      <div class="norma-card__bar is-info"></div>

      <div class="card-body">
        <div class="d-flex align-items-start gap-2 mb-2">
          <div class="norma-icon is-info">
            <svg width="16" height="16" viewBox="0 0 24 24">
              <path d="M12 17v-6m0-3h.01M12 3a9 9 0 1 1 0 18 9 9 0 0 1 0-18Z"
                    stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/>
            </svg>
          </div>

          <div class="flex-grow-1">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <h6 class="mb-0 fw-semibold">{{ $n->titulo }}</h6>
              @if($n->codigo)
                <span class="norma-chip">{{ $n->codigo }}</span>
              @endif
            </div>
            <small class="text-muted">
              @if($scope === 'mine') Aplica a: <strong>{{ $miEstacionNombre }}</strong>
              @elseif($scope === 'general') Aplica: <strong>General</strong>
              @else Aplica a otra estación
              @endif
            </small>
          </div>
        </div>

        @if($n->reglamentacion)
          <div class="norma-block">
            <div class="norma-block__title">Reglamentación</div>
            <div class="norma-block__text">{{ $n->reglamentacion }}</div>
          </div>
        @endif

        {{-- Acciones / Sanciones (sin rojo, tono info) --}}
        <div class="d-flex align-items-center justify-content-between mt-2">
          <span class="norma-badge info">Referencia normativa</span>

          @if($hasSan)
            <button class="btn btn-sm btn-outline-primary"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#{{ $collapseId }}">
              Ver sanciones
            </button>
          @endif
        </div>

        @if($hasSan)
          <div id="{{ $collapseId }}" class="collapse mt-3">
            <div class="sanctions-wrap">
              @foreach($sanList as $i => $san)
                @php
                  // niveles se mantienen por semántica, pero estilizados en azul/gris
                  $nivel = Str::lower($san['nivel'] ?? 'alta');
                  $nivel = in_array($nivel, ['alta','media','baja']) ? $nivel : 'alta';
                @endphp
                <div class="sanction-item info">
                  <div class="sanction-item__head">
                    <div class="sanction-item__title">
                      {{ $san['titulo'] ?? "Sanción #".($i+1) }}
                    </div>
                    <span class="san-chip san-{{ $nivel }}">{{ ucfirst($nivel) }}</span>
                  </div>
                  @if(!empty($san['detalle']))
                    <div class="sanction-item__body">{{ $san['detalle'] }}</div>
                  @endif
                </div>
              @endforeach
            </div>
          </div>
        @endif

      </div>
    </div>
  </div>
@endforeach
  </div>
  @endif
</div>
@endsection