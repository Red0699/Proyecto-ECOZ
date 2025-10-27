@extends('layouts/contentNavbarLayout')

@section('title', 'Editar Usuario')

@section('content')
<h4 class="fw-bold py-3 mb-4">
  <span class="text-muted fw-light">Usuarios /</span> Editar Usuario
</h4>

<div class="row">
  <div class="col-xl">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Datos de {{ $usuario->name }}</h5>
      </div>
      <div class="card-body">
        @include('admin.usuarios._form')
      </div>
    </div>
  </div>
</div>
@endsection