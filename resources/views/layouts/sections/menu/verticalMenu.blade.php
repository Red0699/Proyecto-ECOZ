<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <!-- ! Hide app brand if navbar-full -->
  <div class="app-brand demo mb-3 mt-3">
    <a href="{{url('/')}}" class="app-brand-link">
      <img src="{{ asset('assets/img/logo/LOGO_ECO.png') }}" alt="ECOZ" height="78">
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
      <i class="menu-toggle-icon d-xl-block align-middle"></i>
    </a>
  </div>

  <div class="menu-inner-shadow"></div>

  <ul class="menu-inner py-1">
    @php
    use Illuminate\Support\Facades\Auth;
    $userRole = Auth::check() ? (Auth::user()->role->name ?? '') : null;
    @endphp

    @foreach ($menuData[0]->menu as $index => $menu)

    @php
    // Determinar si este ítem requiere un rol
    $isVisible = !isset($menu->role) || ($userRole === $menu->role);
    @endphp

    {{-- === 1) Encabezado de sección === --}}
    @if (isset($menu->menuHeader))
    @php
    // Mirar si hay al menos un elemento visible después de este header
    $hasVisibleItems = false;
    for ($i = $index + 1; $i < count($menuData[0]->menu); $i++) {
      $next = $menuData[0]->menu[$i];
      if (isset($next->menuHeader)) break; // nuevo header → salir
      $visible = !isset($next->role) || ($userRole === $next->role);
      if ($visible) { $hasVisibleItems = true; break; }
      }
      @endphp

      @if ($hasVisibleItems)
      <li class="menu-header mt-7">
        <span class="menu-header-text">{{ __($menu->menuHeader) }}</span>
      </li>
      @endif

      {{-- === 2) Ítem de menú normal === --}}
      @elseif ($isVisible)
      @php
      $activeClass = '';
      $currentRouteName = Route::currentRouteName();
      if ($currentRouteName === $menu->slug) {
      $activeClass = 'active';
      } elseif (isset($menu->submenu)) {
      if (is_array($menu->slug)) {
      foreach ($menu->slug as $slug) {
      if (str_starts_with($currentRouteName, $slug)) {
      $activeClass = 'active open';
      }
      }
      } else {
      if (str_starts_with($currentRouteName, $menu->slug)) {
      $activeClass = 'active open';
      }
      }
      }
      @endphp

      <li class="menu-item {{ $activeClass }}">
        <a href="{{ isset($menu->url) ? url($menu->url) : 'javascript:void(0);' }}"
          class="{{ isset($menu->submenu) ? 'menu-link menu-toggle' : 'menu-link' }}"
          @if (isset($menu->target) && !empty($menu->target)) target="_blank" @endif>
          @isset($menu->icon)
          <i class="{{ $menu->icon }}"></i>
          @endisset
          <div>{{ __($menu->name ?? '') }}</div>
          @isset($menu->badge)
          <div class="badge bg-{{ $menu->badge[0] }} rounded-pill ms-auto">{{ $menu->badge[1] }}</div>
          @endisset
        </a>

        @isset($menu->submenu)
        @include('layouts.sections.menu.submenu',['menu' => $menu->submenu])
        @endisset
      </li>
      @endif

      @endforeach

  </ul>

</aside>