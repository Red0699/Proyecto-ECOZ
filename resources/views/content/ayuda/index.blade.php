@extends('layouts/contentNavbarLayout')

@section('title', 'Ayuda — Manual de usuario')

@section('page-script')
<script>
  // Visual: mostrar nombre de archivo seleccionado (queda inofensivo si no hay input)
  document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('manualFile');
    const fileLabel = document.getElementById('manualFileName');
    const loadBtn = document.getElementById('btnLoadManual');

    if (input) {
      input.addEventListener('change', function() {
        const f = input.files[0];
        fileLabel.textContent = f ? f.name : 'Ningún archivo seleccionado';
        loadBtn.disabled = !f;
      });
    }

    // Búsqueda en FAQs
    const search = document.getElementById('helpSearch');
    const faqItems = document.querySelectorAll('#faqAccordion .accordion-item');

    if (search) {
      search.addEventListener('input', function() {
        const q = search.value.trim().toLowerCase();
        faqItems.forEach(it => {
          const ok = it.textContent.toLowerCase().includes(q);
          it.style.display = ok ? '' : 'none';
        });
      });
    }
  });
</script>
@endsection

@php

// Alternativa sin Storage (si no quieres usar la Facade):
$candidate = public_path('storage/manuals/manual.pdf');
$manualUrl = file_exists($candidate) ? asset('storage/manuals/manual.pdf') : null;
@endphp


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
        <input id="helpSearch" class="form-control form-control-sm" type="search" placeholder="Buscar en el manual y las preguntas…" aria-label="Buscar">
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
            Usa la búsqueda para saltar rápidamente a cualquier sección del manual o pregunta frecuente.
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

          <div id="manualViewer" class="border rounded p-2" style="min-height:560px; background:linear-gradient(180deg,#fff,#f8fafc);">
            @if(!empty($manualUrl))
            <object data="{{ $manualUrl }}#toolbar=1&navpanes=0&view=FitH" type="application/pdf" width="100%" height="560">
              <embed src="{{ $manualUrl }}#toolbar=1&navpanes=0&view=FitH" type="application/pdf" width="100%" height="560" />
              <iframe src="{{ $manualUrl }}#view=FitH" width="100%" height="560" style="border:0;"></iframe>
              <div class="text-center p-4">
                <i class="ri-file-pdf-2-line ri-2x" style="color:#9ca3af;"></i>
                <p class="small text-muted mb-2">Tu navegador no pudo embeber el PDF.</p>
                <a class="btn btn-sm btn-primary" href="{{ $manualUrl }}" target="_blank" rel="noopener">Abrir manual en nueva pestaña</a>
              </div>
            </object>
            @else
            <div class="text-center p-5">
              <div class="mb-3"><i class="ri-file-pdf-2-line ri-3x" style="color:#9ca3af;"></i></div>
              <h6 class="mb-1">Manual de usuario</h6>
              <p class="small text-muted mb-0">El manual se mostrará aquí cuando sea publicado.</p>
            </div>
            @endif
          </div>

        </div>
      </div>

      <!-- Preguntas frecuentes -->
      <div class="card shadow-sm">
        <div class="card-body">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="mb-0">Preguntas frecuentes</h5>
          </div>

          <div class="accordion" id="faqAccordion">
            <!-- 1 -->
            <div class="accordion-item">
              <h2 class="accordion-header" id="hfaq1">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cfaq1" aria-expanded="false" aria-controls="cfaq1">
                  ¿Qué significan los resultados en COV y CO₂?
                </button>
              </h2>
              <div id="cfaq1" class="accordion-collapse collapse" aria-labelledby="hfaq1" data-bs-parent="#faqAccordion">
                <div class="accordion-body small">
                  Los <strong>COV</strong> representan los compuestos orgánicos volátiles emitidos por la evaporación de la gasolina.
                  El <strong>CO₂e</strong> (equivalente de CO₂) expresa el impacto de esas emisiones en una unidad comparable con otros gases de efecto invernadero.
                </div>
              </div>
            </div>

            <!-- 2 -->
            <div class="accordion-item">
              <h2 class="accordion-header" id="hfaq2">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cfaq2" aria-expanded="false" aria-controls="cfaq2">
                  ¿Cómo debo interpretar las alertas o umbrales que muestra el sistema?
                </button>
              </h2>
              <div id="cfaq2" class="accordion-collapse collapse" aria-labelledby="hfaq2" data-bs-parent="#faqAccordion">
                <div class="accordion-body small">
                  Las alertas se generan cuando ciertos valores (emisiones, pérdidas, niveles de tanque u otros indicadores) se acercan o superan umbrales definidos.
                  Funcionan como señal temprana para <em>revisar operaciones</em>, verificar registros y evaluar acciones de mejora o corrección.
                </div>
              </div>
            </div>

            <!-- 3 -->
            <div class="accordion-item">
              <h2 class="accordion-header" id="hfaq3">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cfaq3" aria-expanded="false" aria-controls="cfaq3">
                  ¿Dónde se almacenan los datos y por cuánto tiempo?
                </button>
              </h2>
              <div id="cfaq3" class="accordion-collapse collapse" aria-labelledby="hfaq3" data-bs-parent="#faqAccordion">
                <div class="accordion-body small">
                  Los datos se guardan en la base de datos del sistema, diseñada para mantener la información de forma segura y organizada.
                  El tiempo de conservación depende de las políticas internas y normas vigentes; por defecto, el sistema los conserva indefinidamente hasta que se disponga lo contrario.
                </div>
              </div>
            </div>

            <!-- 4 -->
            <div class="accordion-item">
              <h2 class="accordion-header" id="hfaq4">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#cfaq4" aria-expanded="false" aria-controls="cfaq4">
                  ¿Con qué frecuencia debo ingresar o cargar la información?
                </button>
              </h2>
              <div id="cfaq4" class="accordion-collapse collapse" aria-labelledby="hfaq4" data-bs-parent="#faqAccordion">
                <div class="accordion-body small">
                  Lo ideal es importar los datos con la misma frecuencia de operación (diaria, por turno o por cierre de tanque).
                  Alternativamente, el sistema puede integrarse a otras plataformas para recibir la información automáticamente y generar reportes sin intervención manual (consultar con soporte técnico).
                </div>
              </div>
            </div>
          </div><!-- /accordion -->
        </div>
      </div>

    </div>
  </div>
</div>
@endsection