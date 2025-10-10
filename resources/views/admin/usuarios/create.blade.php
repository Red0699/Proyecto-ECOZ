{{-- resources/views/admin/usuarios/create.blade.php --}}
@extends('layouts/contentNavbarLayout')

@section('title', 'Añadir Usuario')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Usuarios /</span> Añadir Nuevo
</h4>

<div class="row">
  <div class="col-xl">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Datos del Usuario</h5>
      </div>
      <div class="card-body">
        {{-- Simplemente incluimos nuestro formulario parcial --}}
        @include('admin.usuarios._form')
      </div>
    </div>
  </div>
</div>
@endsection