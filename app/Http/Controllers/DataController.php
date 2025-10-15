<?php

// app/Http/Controllers/DataController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Support\Str;

use App\Models\{DatosHistoricos, Estacion, DataImport};
use App\Imports\{StationDataImport, PreviewImport};
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;

class DataController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $estacion = $user?->estacion;
        if (!$estacion) return back()->with('error', 'Tu usuario no tiene una estación asignada.');

        $q = DatosHistoricos::where('estacion_id', $estacion->id);
        if ($request->filled('fecha'))   $q->whereDate('fecha', $request->date('fecha'));
        if ($request->filled('lote_id')) $q->where('lote_id', (int) $request->lote_id);

        $data  = $q->orderByDesc('fecha')->orderByDesc('hora')->paginate(20)->withQueryString();
        $lotes = \App\Models\DataImport::where('estacion_id', $estacion->id)->orderByDesc('created_at')->limit(30)->get();

        // --- Paginación del modal de preview ---
        $preview      = session()->get('preview', []);
        $previewRows  = $preview['rows']    ?? [];
        $previewHdrs  = $preview['headers'] ?? [];
        $perPage      = max(5, (int) $request->input('preview_per_page', 10));
        $page         = max(1, (int) $request->input('preview_page', 1));
        $total        = count($previewRows);
        $lastPage     = max(1, (int) ceil($total / $perPage));
        $page         = min($page, $lastPage);
        $offset       = ($page - 1) * $perPage;
        $previewSlice = array_slice($previewRows, $offset, $perPage);

        $tempToken = data_get(session()->get('temp_import'), 'token');

        return view('content.data-station.index', [
            'data'        => $data,
            'stationName' => $estacion->nombre,
            'lotes'       => $lotes,
            'loteActual'  => $request->integer('lote_id'),
            'fechaActual' => $request->input('fecha'),
            // Preview paginado
            'previewHeaders' => $previewHdrs,
            'previewRows'    => $previewSlice,
            'previewMeta'    => [
                'page' => $page,
                'perPage' => $perPage,
                'total' => $total,
                'lastPage' => $lastPage,
            ],
            'tempToken'   => $tempToken,
        ]);
    }


    public function store(Request $request)
    {
        $request->validate(['excel_file' => 'required|file|mimes:xlsx,xls|max:20480']);

        $user = Auth::user();
        $estacion = $user?->estacion;
        if (!$estacion) return back()->with('error', 'No tienes estación asignada; no puedes importar.');

        $file = $request->file('excel_file');

        // MODO PREVISUALIZACIÓN → guardar temporal + mostrar modal
        if ($request->boolean('preview_mode')) {
            $limit   = (int) $request->input('preview_rows', 25);
            $token   = (string) Str::uuid();
            $file    = $request->file('excel_file');

            // Guardar temporal (para confirmar luego en el modal)
            $tempExt  = $file->getClientOriginalExtension();
            $tempPath = $file->storeAs("temp_imports/{$estacion->id}", "{$token}.{$tempExt}");

            // Leemos la primera hoja ya con fórmulas calculadas
            $sheets = \Maatwebsite\Excel\Facades\Excel::toArray(new \App\Imports\PreviewImport($limit), $file);
            $sheet  = $sheets[0] ?? [];

            // Encabezados originales
            $headers = array_keys($sheet[0] ?? []);

            // --- 1A) ELIMINAR PRIMERA COLUMNA VACÍA (columna A en blanco) ---
            if (!empty($headers)) {
                $first = $headers[0];
                $normFirst = $this->norm((string) $first);

                // Consideramos "vacío" si el header es '', '—' o un número tipo '0'
                $isHeaderEmpty = ($normFirst === '' || $first === '—' || is_numeric($first));

                // Además, validamos que en las primeras N filas su valor sea vacío
                $firstColHasData = false;
                foreach (array_slice($sheet, 0, 50) as $row) {
                    $val = $row[$first] ?? null;
                    if ($val !== null && $val !== '' && $val !== '—') {
                        $firstColHasData = true;
                        break;
                    }
                }

                if ($isHeaderEmpty && !$firstColHasData) {
                    // quitamos la primera columna
                    array_shift($headers);
                    foreach ($sheet as &$row) {
                        unset($row[$first]);
                    }
                    unset($row);
                }
            }

            // ===== 1B) RECORTAR columnas hasta "presión de sat de tolueno" =====
            $targetAliases = [
                'presion de sat de tolueno',
                'presión de sat de tolueno',
                'presion_sat_tolueno_mmhg',
            ];
            $normHeaders = array_map(fn($h) => $this->norm($h), $headers);
            $cutIdx = -1;
            foreach ($normHeaders as $i => $h) {
                if (in_array($h, $targetAliases, true)) {
                    $cutIdx = $i;
                    break;
                }
            }
            if ($cutIdx === -1) {
                foreach ($normHeaders as $i => $h) {
                    if (str_contains($h, 'tolueno')) {
                        $cutIdx = $i;
                        break;
                    }
                }
            }
            $keepHeaders = $cutIdx >= 0 ? array_slice($headers, 0, $cutIdx + 1) : $headers;

            // ===== 1C) CARRY-FORWARD para celdas combinadas (solo preview) =====
            $filled   = [];
            $lastSeen = []; // header => último valor no vacío
            foreach ($sheet as $row) {
                $newRow = [];
                foreach ($keepHeaders as $h) {
                    $val = $row[$h] ?? null;
                    $isEmpty = $val === null || $val === '' || $val === '—';
                    if ($isEmpty) {
                        if (array_key_exists($h, $lastSeen)) $val = $lastSeen[$h];
                    } else {
                        $lastSeen[$h] = $val;
                    }
                    $newRow[$h] = $val;
                }
                $filled[] = $newRow;
            }

            foreach ($filled as &$row) {
                // --- FECHA ---
                foreach (['fecha', 'Fecha', 'FECHA'] as $key) {
                    if (array_key_exists($key, $row)) {
                        $v = $row[$key];
                        if (is_numeric($v)) {
                            try {
                                $row[$key] = Carbon::instance(XlsDate::excelToDateTimeObject((float)$v))
                                    ->format('Y-m-d');
                            } catch (\Throwable $e) { /* ignora */
                            }
                        } elseif (is_string($v) && strtotime($v)) {
                            $row[$key] = date('Y-m-d', strtotime($v));
                        }
                        break; // si ya lo procesó, salimos
                    }
                }

                // --- HORA ---
                foreach (['hora', 'Hora', 'HORA'] as $key) {
                    if (array_key_exists($key, $row)) {
                        $v = $row[$key];
                        if (is_numeric($v)) {
                            $sec = (int)round(((float)$v) * 86400);
                            $row[$key] = gmdate('H:i:s', $sec);
                        } elseif (is_string($v) && strtotime($v)) {
                            $row[$key] = date('H:i:s', strtotime($v));
                        }
                        break;
                    }
                }
            }
            unset($row);




            // Persistimos para el modal
            session()->put('preview', [
                'headers' => $keepHeaders,
                'rows'    => $filled,
            ]);
            session()->put('temp_import', [
                'token'       => $token,
                'path'        => $tempPath,
                'original'    => $file->getClientOriginalName(),
                'mime'        => $file->getClientMimeType(),
                'size'        => $file->getSize(),
                'estacion_id' => $estacion->id,
                'user_id'     => $user->id,
            ]);

            return redirect()->route('datos.index')->with('success', 'Previsualización generada.');
        }

        // IMPORTACIÓN DIRECTA (sin preview)
        try {
            // crear lote
            $lote = \App\Models\DataImport::create([
                'estacion_id' => $estacion->id,
                'user_id'     => $user->id,
                'archivo'     => $file->getClientOriginalName(),
                'mime'        => $file->getClientMimeType(),
                'size'        => $file->getSize(),
                'estado'      => 'ok',
                'filas'       => 0,
            ]);
            // importar
            Excel::import(new StationDataImport($estacion->id, $lote->id), $file);
            $count = DatosHistoricos::where('lote_id', $lote->id)->count();
            $lote->update(['filas' => $count]);

            return redirect()->route('datos.index', ['lote_id' => $lote->id])
                ->with('success', "¡Importado! Lote #{$lote->id} con {$count} filas.");
        } catch (ValidationException $e) {
            $msgs = collect($e->failures())->map(fn($f) => 'Fila ' . $f->row() . ': ' . implode('; ', $f->errors()))->take(8)->implode(' | ');
            return back()->with('error', 'Errores de validación: ' . $msgs);
        } catch (\Throwable $e) {
            return back()->with('error', 'Error al importar: ' . $e->getMessage());
        }
    }

    public function lotes()
    {
        $user = Auth::user();
        $estacion = $user?->estacion;
        if (!$estacion) return back()->with('error', 'Sin estación');
        $lotes = DataImport::where('estacion_id', $estacion->id)->orderByDesc('created_at')->paginate(20);
        return view('content.data-station.lotes', compact('lotes'));
    }

    public function destroyLote(int $id)
    {
        $user = Auth::user();
        $estacion = $user?->estacion;
        if (!$estacion) return back()->with('error', 'Sin estación');

        $lote = DataImport::where('estacion_id', $estacion->id)->findOrFail($id);
        // borra archivo guardado
        if ($lote->path && Storage::exists($lote->path)) Storage::delete($lote->path);
        $lote->delete(); // cascade borra datos_historicos
        return back()->with('success', "Lote #{$id} eliminado.");
    }

    public function confirmPreviewImport(Request $request)
    {
        $temp = session('temp_import');
        if (!$temp || empty($temp['path']) || !Storage::exists($temp['path'])) {
            return redirect()->route('datos.index')->with('error', 'No se encontró el archivo temporal de previsualización.');
        }

        // Seguridad básica: confirmar estación/usuario coinciden
        $user = Auth::user();
        if ((int)$temp['user_id'] !== (int)$user->id) {
            return redirect()->route('datos.index')->with('error', 'Sesión inválida para confirmar importación.');
        }

        $estacionId = (int) $temp['estacion_id'];

        // Crear lote
        $lote = \App\Models\DataImport::create([
            'estacion_id' => $estacionId,
            'user_id'     => $user->id,
            'archivo'     => $temp['original'] ?? 'archivo.xlsx',
            'mime'        => $temp['mime'] ?? null,
            'size'        => $temp['size'] ?? null,
            'path'        => $temp['path'],
            'estado'      => 'ok',
            'filas'       => 0,
        ]);

        try {
            // Importar desde archivo temporal
            Excel::import(new StationDataImport($estacionId, $lote->id), Storage::path($temp['path']));
            $count = DatosHistoricos::where('lote_id', $lote->id)->count();
            $lote->update(['filas' => $count]);

            // Limpiar sesión de preview
            session()->forget('preview');
            session()->forget('temp_import');

            return redirect()->route('datos.index', ['lote_id' => $lote->id])
                ->with('success', "¡Importado! Lote #{$lote->id} con {$count} filas.");
        } catch (\Throwable $e) {
            return redirect()->route('datos.index')->with('error', 'Error al importar: ' . $e->getMessage());
        }
    }

    public function cancelPreviewImport(Request $request)
    {
        $temp = session('temp_import');
        if ($temp && !empty($temp['path']) && Storage::exists($temp['path'])) {
            Storage::delete($temp['path']);
        }
        session()->forget('preview');
        session()->forget('temp_import');

        return redirect()->route('datos.index')->with('success', 'Importación cancelada. No se guardaron datos.');
    }

    private function norm(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        $t = preg_replace('/[^a-z0-9]+/i', ' ', $t);
        return trim(preg_replace('/\s+/', ' ', $t));
    }
}
