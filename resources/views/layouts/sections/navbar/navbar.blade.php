@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
$containerNav = $containerNav ?? 'container-fluid';
$navbarDetached = ($navbarDetached ?? '');

@endphp

@php
// Fallback por si el View Composer no inyectó variables
if (!isset($navAlerts) || !is_array($navAlerts)) {
$navAlerts = \App\Support\AlertsBuilder::forUserNavbar(\Illuminate\Support\Facades\Auth::user());
}
$navAlertsCount = $navAlertsCount ?? count($navAlerts ?? []);
@endphp

<!-- Navbar -->
@if(isset($navbarDetached) && $navbarDetached == 'navbar-detached')
<nav class="layout-navbar {{$containerNav}} navbar navbar-expand-xl {{$navbarDetached}} align-items-center bg-navbar-theme" id="layout-navbar">
  @endif
  @if(isset($navbarDetached) && $navbarDetached == '')
  <nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
    <div class="{{$containerNav}}">
      @endif

      <!--  Brand demo (display only for navbar-full and hide on below xl) -->
      @if(isset($navbarFull))
      <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-6">
        <a href="{{url('/')}}" class="app-brand-link gap-2">
          <img src="{{ asset('assets/img/logo/LOGO_ECO.png') }}" alt="ECOZ" height="100">
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-xl-none">
          <i class="ri-close-fill align-middle"></i>
        </a>
      </div>
      @endif

      <!-- ! Not required for layout-without-menu -->
      @if(!isset($navbarHideToggle))
      <div class="layout-menu-toggle navbar-nav align-items-xl-center me-4 me-xl-0{{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ?' d-xl-none ' : '' }}">
        <a class="nav-item nav-link px-0 me-xl-6" href="javascript:void(0)">
          <i class="ri-menu-fill ri-24px"></i>
        </a>
      </div>
      @endif

      <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <!-- Search -->
        <div class="navbar-nav align-items-center">
          <div class="nav-item d-flex align-items-center">
            <i class="ri-search-line ri-22px me-1_5"></i>
            <input type="text" class="form-control border-0 shadow-none ps-1 ps-sm-2 ms-50" placeholder="Buscar..." aria-label="Search...">
          </div>
        </div>
        <!-- /Search -->
        <ul class="navbar-nav flex-row align-items-center ms-auto">

          <!-- Notificaciones -->
          <li class="nav-item dropdown me-2 px-2">
            <a class="nav-link position-relative p-0"
              id="notifDropdown"
              href="javascript:void(0);"
              role="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
              aria-label="Notificaciones"
              data-key="ecoz:notif:last-seen:{{ Auth::id() }}"
              data-count="{{ $navAlertsCount ?? 0 }}">
              <i class="ri-notification-3-line ri-28px"></i>

              @if(($navAlertsCount ?? 0) > 0)
              <span class="notifbell-dot" title="Tienes notificaciones nuevas"></span>
              @endif
            </a>

            <div class="dropdown-menu dropdown-menu-end notifpro-dropdown mt-3 p-0" aria-labelledby="notifDropdown">
              {{-- Header --}}
              <div class="notifpro-head d-flex align-items-center justify-content-between px-3 py-2">
                <div class="d-flex align-items-center gap-2">
                  <i class="ri-notification-3-fill text-primary"></i>
                  <span class="notifpro-title">Notificaciones</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                  <span class="notifpro-pill">{{ $navAlertsCount ?? 0 }} nuevas</span>
                </div>
              </div>
              <div class="notifpro-sep"></div>

              {{-- Lista --}}
              <div class="notifpro-list" role="list">
                @forelse(($navAlerts ?? []) as $a)
                @php
                $sev = $a['severity'] ?? 'info';
                $sent = $a['sent_at_str'] ?? null;
                $safeId = $a['id'] ?? md5(($a['norma'] ?? '') . '|' . ($a['sent_at'] ?? microtime(true)));
                $chip = $sev === 'danger' ? 'chip-danger' : ($sev === 'warning' ? 'chip-warning' : 'chip-info');
                @endphp

                <a role="listitem"
                  class="notifpro-item d-flex align-items-start gap-3 px-3 py-3"
                  href="{{ route('inicio') }}#notificaciones"
                  data-alert-id="{{ $safeId }}"
                  data-unread="true">
                  {{-- Avatar/ícono --}}
                  <div class="notifpro-avatar {{ $sev }}">
                    @if($sev === 'danger') <i class="ri-error-warning-line"></i>
                    @elseif($sev === 'warning') <i class="ri-alert-line"></i>
                    @else <i class="ri-information-line"></i>
                    @endif
                  </div>

                  {{-- Contenido --}}
                  <div class="flex-grow-1 min-w-0">
                    <div class="notifpro-title-2 text-truncate">{{ $a['norma'] ?? 'Alerta' }}</div>

                    @if(!empty($a['mensaje']))
                    <div class="notifpro-text text-truncate-2">{{ $a['mensaje'] }}</div>
                    @endif

                    <div class="notifpro-tags">
                      <span class="notifpro-chip {{ $chip }}">
                        {{ $sev === 'danger' ? 'Crítica' : ($sev === 'warning' ? 'Atención' : 'Info') }}
                      </span>
                      @if($sent)<span class="notifpro-date" title="Enviado: {{ $sent }}">{{ $sent }}</span>@endif
                    </div>
                  </div>

                  {{-- Dot de no leído --}}
                  <span class="notifpro-dot {{ 'dot-'.$sev }}"></span>
                </a>

                @empty
                <div class="px-3 py-4 text-center">
                  <div class="notifpro-empty-emoji">🎉</div>
                  <div class="text-muted small">No tienes notificaciones</div>
                </div>
                @endforelse
              </div>

              <div class="notifpro-sep"></div>
              {{-- Footer --}}
              <div class="px-3 py-2">
                <a href="{{ route('inicio') }}#notificaciones" class="btn btn-sm btn-primary w-100">
                  Ver todas las notificaciones
                </a>
              </div>
            </div>
          </li>

          @push('scripts')
          <script>
            document.addEventListener('DOMContentLoaded', () => {
              const trigger = document.getElementById('notifDropdown');
              if (!trigger) return;

              const dot = trigger.querySelector('.notifbell-dot');
              const key = trigger.dataset.key || 'ecoz:notif:last-seen';
              const countNow = Number(trigger.dataset.count || 0);
              const today = new Date().toISOString().slice(0, 10); // YYYY-MM-DD

              function getStored() {
                try {
                  return JSON.parse(localStorage.getItem(key) || '{}');
                } catch {
                  return {};
                }
              }

              function setStored(v) {
                localStorage.setItem(key, JSON.stringify(v));
              }

              function hideDot() {
                if (dot) dot.classList.add('d-none');
              }

              // Al cargar: si ya se vieron HOY y el conteo coincide, apaga el dot
              const saved = getStored();
              if (saved.date === today && Number(saved.count) === countNow) {
                hideDot();
              }

              // Al abrir realmente el dropdown (evento Bootstrap)
              trigger.addEventListener('shown.bs.dropdown', () => {
                setStored({
                  date: today,
                  count: countNow
                });
                hideDot();
              });

              // Fallback: si alguien hace click directo (por si otro tema evita el evento)
              trigger.addEventListener('click', () => {
                setStored({
                  date: today,
                  count: countNow
                });
                hideDot();
              });
            });
          </script>
          @endpush

          <!-- User -->
          <li class="nav-item navbar-dropdown dropdown-user dropdown">
            @php
            $user = Auth::user();
            $estacionNombre = strtoupper($user->estacion->nombre ?? '');
            $avatarFile = match ($estacionNombre) {
            'COTA' => 'EDS-COTA.jpg',
            'SILVANIA' => 'EDS-SILVANIA.jpg',
            'UBATÉ', 'UBATE' => 'EDS-UBATE.jpg',
            default => '1.png', // fallback
            };
            @endphp

            <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
              <div class="avatar avatar-online">
                <img src="{{ asset('assets/img/avatars/' . $avatarFile) }}"
                  alt="{{ $user->name }}"
                  class="w-px-60 h-60 rounded-circle">
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end mt-3 py-2">
              <li>
                <a class="dropdown-item" href="javascript:void(0);">
                  <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-2">
                      <div class="avatar avatar-online">
                        <img src="{{ asset('assets/img/avatars/' . $avatarFile) }}"
                          alt="{{ $user->name }}"
                          class="w-px-60 h-60 rounded-circle">
                      </div>
                    </div>
                    <div class="flex-grow-1">
                      <h6 class="mb-0 small">{{ Auth::user()->name ?? 'Invitado' }}</h6>
                      <small class="text-muted">
                        {{ Auth::user()->role === 'Administrador' ? 'Administrador' : 'Usuario' }}
                      </small>
                    </div>
                  </div>
                </a>
              </li>
              <li>
                <div class="dropdown-divider"></div>
              </li>
              <li>
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                  <i class="ri-user-settings-line ri-22px me-2"></i>
                  <span class="align-middle">Mi perfil</span>
                </a>
              </li>
              <!--
              <li>
                <a class="dropdown-item" href="javascript:void(0);">
                  <i class='ri-settings-4-line ri-22px me-2'></i>
                  <span class="align-middle">Settings</span>
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="javascript:void(0);">
                  <span class="d-flex align-items-center align-middle">
                    <i class="flex-shrink-0 ri-file-text-line ri-22px me-3"></i>
                    <span class="flex-grow-1 align-middle">Billing</span>
                    <span class="flex-shrink-0 badge badge-center rounded-pill bg-danger h-px-20 d-flex align-items-center justify-content-center">4</span>
                  </span>
                </a>
              </li>
              -->
              <li>
                <div class="dropdown-divider"></div>
              </li>
              <li>
                <div class="d-grid px-4 pt-2 pb-1">
                  <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-danger d-flex w-100">
                      <small class="align-middle">Cerrar sesión</small>
                      <i class="ri-logout-box-r-line ms-2 ri-16px"></i>
                    </button>
                  </form>
                </div>
              </li>

            </ul>
          </li>
          <!--/ User -->
        </ul>
      </div>

      @if(!isset($navbarDetached))
    </div>
    @endif
  </nav>
  <!-- / Navbar -->