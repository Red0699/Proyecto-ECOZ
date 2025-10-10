<?php

// app/Http/Middleware/IsAdmin.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica si el usuario está autenticado y si su rol es admin
        if (Auth::check() && Auth::user()->role->name == 'admin') {
            return $next($request); 
        }

        return redirect('/dashboard')->with('error', 'Acceso no autorizado.');
    }
}
