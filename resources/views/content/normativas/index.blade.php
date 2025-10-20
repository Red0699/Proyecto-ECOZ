@extends('layouts/contentNavbarLayout')
@section('title','Normativas')

@section('content')
<div class="container-xxl py-3">
  <div class="d-flex align-items-center justify-content-between mb-2">
    <h6 class="mb-0">Normativas aplicables</h6>
    <small class="text-muted">Por estación o generales</small>
  </div>

  @if($normativas->isEmpty())
    <div class="alert alert-info">No hay normativas registradas.</div>
  @else
    <div class="row g-2">
      @foreach($normativas as $n)
        <div class="col-12 col-lg-6">
          <div class="card h-100 shadow-sm">
            <div class="card-body">
              <h6 class="mb-1">{{ $n->titulo }}</h6>
              @if($n->codigo)
                <small class="text-muted d-block mb-1">{{ $n->codigo }}</small>
              @endif
              @if($n->reglamentacion)
                <p class="mb-2"><strong>Reglamentación:</strong> {{ $n->reglamentacion }}</p>
              @endif
              @if($n->sanciones)
                <p class="mb-0"><strong>Sanciones:</strong> {{ $n->sanciones }}</p>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif
</div>
@endsection
