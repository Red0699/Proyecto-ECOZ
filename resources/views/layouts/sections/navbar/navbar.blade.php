@php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
$containerNav = $containerNav ?? 'container-fluid';
$navbarDetached = ($navbarDetached ?? '');

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

          <!--   
          <li class="nav-item lh-1 me-4">
            <a class="github-button" href="{{config('variables.repository')}}" data-icon="octicon-star" data-size="large" data-show-count="true" aria-label="Star themeselection/materio-html-laravel-admin-template-free on GitHub">Star</a>
          </li>
          -->

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
                <a class="dropdown-item" href="javascript:void(0);">
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