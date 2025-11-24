@extends('layouts/blankLayout')

@section('title', 'Login')

@section('page-style')
@vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
<style>
  .field-error {
    margin-bottom: .35rem;
    font-size: .875rem;
    color: #dc3545;
  }

  .form-label {
    margin-bottom: .35rem;
  }

  .input-group .form-control.is-invalid {
    border-color: #dc3545;
    box-shadow: 0 0 0 .2rem rgba(220, 53, 69, .08);
  }
</style>
@endsection

@section('content')
<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6 mx-4">

      <div class="card p-7">
        <!-- Logo -->
        <div class="app-brand justify-content-center mt-5">
          <span class="app-brand-logo demo">
            <img src="{{ asset('assets/img/logo/LOGO_ECO.png') }}" alt="ECOZ" height="100">
          </span>
        </div>
        <!-- /Logo -->

        <div class="card-body mt-1">
          <p class="mb-5">Accede a tu cuenta para empezar</p>

          {{-- Errores generales no asociados a un campo --}}
          @if ($errors->any())
          @php
          $nonFieldErrors = collect($errors->all())
          ->reject(fn($e) => str_contains(Str::lower($e), 'correo') || str_contains(Str::lower($e), 'contraseña'));
          @endphp
          @if($nonFieldErrors->isNotEmpty())
          <div class="alert alert-danger mb-4">
            <ul class="mb-0">
              @foreach ($nonFieldErrors as $error)
              <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
          @endif
          @endif

          <form id="formAuthentication" class="mb-5" method="POST" action="{{ route('login.post') }}">
            @csrf

            {{-- EMAIL --}}
            <div class="mb-4">
              <label for="email" class="form-label fw-medium">Correo electrónico</label>
              <input
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                name="email"
                placeholder="usuario@ejemplo.com"
                value="{{ old('email') }}"
                autofocus
                autocomplete="username">
            </div>

            {{-- PASSWORD --}}
            <div class="mb-4">
              <label for="password" class="form-label fw-medium">Contraseña</label>
              <div class="input-group input-group-merge">
                <input
                  type="password"
                  id="password"
                  class="form-control @error('password') is-invalid @enderror"
                  name="password"
                  placeholder="••••••••"
                  aria-describedby="password"
                  autocomplete="current-password" />
              </div>
            </div>

            <div class="mb-5 pb-2 d-flex justify-content-between pt-1 align-items-center">
              <div class="form-check mb-0">
                <input class="form-check-input" type="checkbox" id="remember-me" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                <label class="form-check-label" for="remember-me">Recuérdame</label>
              </div>

              <a href="{{ route('forgot.show') }}" class="float-end mb-1">
                <span>Olvidé mi contraseña</span>
              </a>
            </div>

            <div class="mb-5">
              <button class="btn btn-primary d-grid w-100" type="submit">Ingresar</button>
            </div>
          </form>

        </div>
      </div>

    </div>
  </div>
</div>


@endsection