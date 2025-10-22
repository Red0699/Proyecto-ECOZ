@extends('layouts/contentNavbarLayout')
@section('title','Acerca del sistema')

@section('page-style')
@vite('resources/assets/vendor/scss/pages/page-acerca.scss')
@endsection

@section('content')
<div class="container-xxl about-eco2z py-4">
  <!-- Hero / Encabezado visual -->
  <div class="about-hero card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="about-hero__bg"></div>
    <div class="about-hero__body p-4 p-md-5">
      <div class="d-flex align-items-center gap-3 mb-3">
        <div class="about-logo d-inline-flex align-items-center justify-content-center rounded-3">
          {{-- Logo / ícono del sistema (puedes reemplazar por tu SVG) --}}
          <i class="ri-leaf-line fs-2"></i>
        </div>
        <div>
          <h1 class="mb-1">Calculadora ECO₂Z</h1>
          <p class="mb-0 text-muted">Monitoreo y cuantificación de COV y CO₂ en tanques de gasolina — EDS</p>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-lg-8">
          <div class="about-copy pe-lg-4">
            <p class="lead mb-3">
              Sistema web desarrollado para la cuantificación y monitoreo de emisiones de
              <strong>Compuestos Orgánicos Volátiles (COV)</strong> y <strong>Dióxido de Carbono (CO₂)</strong>
              generadas por la evaporación de gasolina en tanques de almacenamiento de estaciones de servicio (EDS),
              con el fin de proporcionar una herramienta confiable y automatizada para el control ambiental de estas fuentes de emisión.
            </p>
            <p class="mb-3">
              Tiene como objetivo estimar las <strong>pérdidas por respiración y trabajo en el tanque</strong>,
              calculando las <strong>presiones internas</strong> y <strong>variaciones volumétricas</strong> asociadas al comportamiento del combustible.
              De esta manera, facilita la evaluación integral del impacto ambiental y promueve la
              <strong>toma de decisiones basadas en datos</strong> para mejorar la eficiencia operativa.
            </p>
          </div>
        </div>
        <div class="col-lg-4">
          <!-- Chips / etiquetas -->
          <div class="d-flex flex-wrap gap-2">
            <span class="badge bg-label-primary rounded-pill">COV → CO₂</span>
            <span class="badge bg-label-success rounded-pill">ApexCharts</span>
            <span class="badge bg-label-info rounded-pill">Laravel</span>
            <span class="badge bg-label-secondary rounded-pill">MySQL</span>
            <span class="badge bg-label-danger rounded-pill">Control ambiental</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bloques informativos -->
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card h-100 about-info shadow-sm">
        <div class="card-body">
          <div class="icon-wrap mb-3">
            <i class="ri-gas-station-line"></i>
          </div>
          <h5 class="mb-2">Respiración y trabajo</h5>
          <p class="text-muted mb-0">
            Estimación de pérdidas por respiración diaria y por operaciones de abastecimiento/descargue,
            integrando comportamiento de presión y volumen del tanque.
          </p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100 about-info shadow-sm">
        <div class="card-body">
          <div class="icon-wrap mb-3">
            <i class="ri-bar-chart-2-line"></i>
          </div>
          <h5 class="mb-2">Datos y visualización</h5>
          <p class="text-muted mb-0">
            Cálculos reproducibles y visualizaciones claras.
          </p>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card h-100 about-info shadow-sm">
        <div class="card-body">
          <div class="icon-wrap mb-3">
            <i class="ri-shield-check-line"></i>
          </div>
          <h5 class="mb-2">Enfoque normativo</h5>
          <p class="text-muted mb-0">
            Alineado con parámetros legales y buenas prácticas de medición para fortalecer el control ambiental en EDS.
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Créditos / contacto -->
  <div class="card mt-4 shadow-sm">
    <div class="card-body d-flex flex-column flex-md-row align-items-center justify-content-between flex-wrap gap-3 about-footer">
      <div class="about-footer__left">
        <h6 class="text-muted mb-2 fw-semibold">Créditos</h6>
        <div class="d-flex flex-wrap gap-2">
          <span class="chip"><i class="ri-user-3-line me-1"></i> Gutierrez Giselle & Campos Karenn</span>
          <span class="chip"><i class="ri-graduation-cap-line me-1"></i> Proyecto académico de desarrollo web</span>
          <span class="chip"><i class="ri-calendar-line me-1"></i> 2025</span>
        </div>
      </div>

      <div class="about-footer__right text-md-end">
        <h6 class="text-muted mb-2 fw-semibold">Sugerencias o errores</h6>
        <a class="btn btn-primary btn-sm" href="mailto:ecoz@gmail.com">
          <i class="ri-mail-send-line me-1"></i> ecozsoporte@gmail.com
        </a>
      </div>

    </div>
  </div>
</div>
@endsection