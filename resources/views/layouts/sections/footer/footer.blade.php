@php
$containerFooter = !empty($containerNav) ? $containerNav : 'container-fluid';
@endphp

<!-- Footer -->
<footer class="content-footer footer bg-footer-theme border-top">
  <div class="{{ $containerFooter }}">
    <div class="footer-container d-flex flex-column flex-md-row align-items-center justify-content-between py-3">

      {{-- Texto principal --}}
      <div class="text-center text-md-start text-body mb-2 mb-md-0">
        <strong>ECO₂Z</strong> © {{ date('Y') }}
        — Proyecto con <span class="text-success fw-semibold">todos los derechos reservados</span>.
      </div>

      {{-- Línea secundaria opcional --}}
      <div class="text-center text-md-end small text-muted">
        Creado en {{ config('app.name') ?? 'Laravel' }} ·
        <span class="fw-semibold">Desde {{ config('app.start_year', '2025') }}</span>
      </div>

    </div>
  </div>
</footer>
<!--/ Footer -->
