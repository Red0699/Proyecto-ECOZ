<?php

namespace App\Http\Controllers;

use App\Models\Normativa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NormativaController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $normativas = Normativa::query()
            ->when($user?->estacion_id, function($q) use ($user) {
                $q->whereNull('estacion_id')
                  ->orWhere('estacion_id', $user->estacion_id);
            }, function($q) {
                // si no tiene estación, muestra todas
            })
            ->orderBy('codigo')
            ->get();

        return view('content.normativas.index', compact('normativas'));
    }
}
