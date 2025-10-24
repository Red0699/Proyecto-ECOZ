@extends('layouts/contentNavbarLayout')

@section('title', 'Ayuda — Manual de usuario')

@section('page-script')
  <script>
    // Visual: mostrar nombre de archivo seleccionado
    document.addEventListener('DOMContentLoaded', function () {
      const input = document.getElementById('manualFile');
      const fileLabel = document.getElementById('manualFileName');
      const loadBtn = document.getElementById('btnLoadManual');

      if (input) {
        input.addEventListener('change', function () {
          const f = input.files[0];
          fileLabel.textContent = f ? f.name : 'Ningún archivo seleccionado';
          loadBtn.disabled = !f;
        });
      }
    });
  </script>
@endsection

@section('content')
<div class="container-xxl py-4 hrx">
  <div class="d-flex align-items-start justify-content-between mb-4">
    <div>
      <h3 class="mb-1">Centro de Ayuda</h3>
      <div class="text-muted small">Manual de usuario y guías rápidas para usar ECOZ</div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <div class="input-group input-group-sm">
        <span class="input-group-text bg-white"><i class="ri-search-line"></i></span>
        <input class="form-control form-control-sm" type="search" placeholder="Buscar en el manual..." aria-label="Buscar">
      </div>
    </div>
  </div>

  <div class="row g-3">
    <!-- Sidebar: temas / índice -->
    <div class="col-12 col-lg-4">
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between notif-head mb-3">
            <div>
              <div class="notif-title">Índice rápido</div>
              <div class="small text-muted">Navega por los temas más consultados</div>
            </div>
            <div class="notif-pill">v1.0</div>
          </div>

          <div class="accordion" id="helpIndex">
            
            <div class="accordion-item notif-item">
              <h2 class="accordion-header" id="headingTwo">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                  Carga de datos
                </button>
              </h2>
              <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#helpIndex">
                <div class="accordion-body small notif-item-text">
                  Formatos, previsualización y confirmación de importes.
                </div>
              </div>
            </div>

            <div class="accordion-item notif-item">
              <h2 class="accordion-header" id="headingThree">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                  Notificaciones y alertas
                </button>
              </h2>
              <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#helpIndex">
                <div class="accordion-body small notif-item-text">
                  Configuración de alertas por inventario y canales de envío.
                </div>
              </div>
            </div>
          </div>

          <hr>

          <div class="small text-muted mb-2">Recursos</div>
          <ul class="list-group list-group-flush">
            <li class="list-group-item d-flex justify-content-between align-items-center small">
              Guía rápida (PDF)
              <span class="badge bg-primary rounded-pill">Descargar</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center small">
              Preguntas frecuentes
              <span class="text-muted small">4</span>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-center small">
              Contactar soporte
              <span class="text-muted small">ecozsoporte@gmail.com</span>
            </li>
          </ul>
        </div>
      </div>

      <div class="card shadow-sm">
        <div class="card-body">
          <h6 class="mb-2 notif-item-title">Consejo rápido</h6>
          <p class="small text-muted notif-item-text">
            Usa la búsqueda para saltar rápidamente a cualquier sección del manual. El contenido del manual se cargará aquí cuando esté disponible.
          </p>
        </div>
      </div>
    </div>

    <!-- Main: visor de manual -->
    <div class="col-12 col-lg-8">
      <div class="card shadow-sm mb-3">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
              <h5 class="mb-0">Manual de usuario</h5>
              <div class="small text-muted">Sección principal</div>
            </div>
          </div>

          <div id="manualViewer" class="border rounded p-4 text-center" style="min-height:360px; background: linear-gradient(180deg,#fff,#f8fafc);">
            <div class="mb-3">
              <i class="ri-file-ppt-2-line ri-3x" style="color:#9ca3af;"></i>
            </div>
            <h6 class="mb-1">Manual de usuario</h6>
            <p class="small text-muted">El manual se mostrará aquí cuando sea publicado.</p>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
@endsection