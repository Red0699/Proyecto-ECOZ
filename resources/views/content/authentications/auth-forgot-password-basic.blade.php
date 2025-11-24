{{-- resources/views/auth/forgot-password.blade.php --}}
@extends('layouts/blankLayout')

@section('title','Olvidé mi contraseña')
@section('page-style')
  @vite(['resources/assets/vendor/scss/pages/page-auth.scss'])
@endsection

@section('content')
<div class="position-relative">
  <div class="authentication-wrapper authentication-basic container-p-y">
    <div class="authentication-inner py-6 mx-4">
      <div class="card p-7">
        <div class="app-brand justify-content-center mt-5">
          <a href="{{ url('/') }}" class="app-brand-link gap-3">
            <img src="{{ asset('assets/img/logo/LOGO_ECO.png') }}" alt="ECOZ" height="56">
          </a>
        </div>

        <div class="card-body mt-1">
          <h4 class="mb-1">¿Olvidaste tu contraseña? 🔒</h4>
          <p class="mb-5">Ingresa tu correo. Si existe, notificaremos al administrador para actualizar tu clave.</p>

          @if (session('status'))
            <div class="alert alert-success mb-4">{{ session('status') }}</div>
          @endif
          @error('email')
            <div class="alert alert-danger mb-4">{{ $message }}</div>
          @enderror

          <form class="mb-5" method="POST" action="{{ route('forgot.submit') }}">
            @csrf
            <div class="mb-5">
              <label for="email" class="form-label fw-medium">Correo electrónico</label>
              <input type="email" class="form-control" id="email" name="email" placeholder="usuario@ejemplo.com" autofocus>
              
            </div>
            <button class="btn btn-primary d-grid w-100 mb-5">Enviar solicitud</button>
          </form>

          <div class="text-center">
            <a href="{{ route('login') }}" class="d-flex align-items-center justify-content-center">
              <i class="ri-arrow-left-s-line ri-20px me-1_5"></i>
              Volver al inicio de sesión
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
