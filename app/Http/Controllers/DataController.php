<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

use App\Models\DatosHistoricos;
use App\Models\Estacion;
use App\Imports\StationDataImport; // Import the StationDataImport class

class DataController extends Controller
{
    public function index(Request $request)
    {
        $usuario  = Auth::user();                 // App\Models\Usuario
        $estacion = $usuario?->estacion;          // relación ->estacion()

        if (!$estacion) {
            return back()->with('error', 'Tu usuario no tiene una estación asignada.');
        }

        $q = DatosHistoricos::query()->where('estacion_id', $estacion->id);

        if ($request->filled('fecha')) {
            $q->whereDate('fecha', $request->date('fecha'));
        }

        $data = $q->orderByDesc('fecha')
                  ->orderByDesc('hora')
                  ->paginate(20);

        $nombreEstacion = $estacion->nombre;

        return view('content.data-station.index', [
            'data'          => $data,
            'stationName'   => $nombreEstacion, // para reusar tu Blade actual
        ]);
    }

    public function store(Request $request)
    {
        // Aquí puedes añadir $this->authorize('import', DatosHistoricos::class); si usas Policy
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:20480',
        ]);

        $usuario  = Auth::user();
        $estacion = $usuario?->estacion;

        if (!$estacion) {
            return back()->with('error', 'No tienes estación asignada; no puedes importar.');
        }

        try {
            Excel::import(new StationDataImport($estacion->id), $request->file('excel_file'));
        } catch (ValidationException $e) {
            $msgs = collect($e->failures())->map(function ($f) {
                return 'Fila ' . $f->row() . ': ' . implode('; ', $f->errors());
            })->take(10)->implode(' | ');
            return back()->with('error', 'Errores de validación en el archivo: ' . $msgs);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al importar: ' . $e->getMessage());
        }

        return back()->with('success', '¡Archivo importado! Registros creados/actualizados correctamente.');
    }
}
