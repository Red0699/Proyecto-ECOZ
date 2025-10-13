<?php

namespace App\Imports;

use App\Models\DatosHistoricos;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\{
    OnEachRow,
    WithHeadingRow,
    WithChunkReading,
    WithEvents,
    WithUpserts
};
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as XlsDate;

class StationDataImport implements OnEachRow, WithHeadingRow, WithChunkReading, WithEvents, WithUpserts
{
    use \Maatwebsite\Excel\Concerns\Importable;

    public function __construct(private int $estacionId) {}

    /** ====== Ajustes performance/clave única ====== */
    public function chunkSize(): int
    {
        return 1000;
    }
    public function uniqueBy()
    {
        return ['estacion_id', 'fecha', 'hora'];
    }
    public function upsertColumns()
    {
        return [
            'temperatura_ambiente_c',
            'velocidad_viento',
            'humedad_ambiente',
            'temperatura_interna_c',
            'temperatura_interna_k',
            'volumen_gl',
            'descargue_combustible_gl',
            'ventas_diarias_gl',
            'diametro_tanque_in',
            'presion_hidrostatica_pa',
            'presion_hidrostatica_kpa',
            'presion_psi',
            'perdidas_respiracion_kg',
            'variacion_formula_gl_1',
            'perdidas_operacion_kg',
            'variacion_formula_gl_2',
            'perdidas_totales_cov_kg',
            'cov_a_co2_kg',
            'sumatoria_variacion_gl',
            'variacion_eds_gl',
            'presion_sat_octano_mmhg',
            'presion_sat_heptano_mmhg',
            'presion_sat_tolueno_mmhg',
            'updated_at',
        ];
    }

    /** ====== Soporte a fórmulas cross-sheet ====== */
    private Worksheet $sheet;
    private int $headingRow = 1;   // cambia si tu encabezado no está en la fila 1
    private array $headerIndex = []; // key normalizada -> columna (1-based)

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                // Worksheet del sheet que se está procesando
                $this->sheet = $event->getSheet()->getDelegate();

                // Construye índice de encabezados leyendo la fila de encabezado
                $highestCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($this->sheet->getHighestColumn());
                $this->headerIndex = [];
                for ($col = 1; $col <= $highestCol; $col++) {
                    $label = (string) $this->sheet->getCellByColumnAndRow($col, $this->headingRow)->getValue();
                    $this->headerIndex[$this->norm($label)] = $col;
                }
            },
        ];
    }

    /** ====== Normalización, parseo y carry-forward ====== */
    private array $carry = [];
    private array $carryFields = [
        'diametro_tanque_in',
        'presion_sat_octano_mmhg',
        'presion_sat_heptano_mmhg',
        'presion_sat_tolueno_mmhg',
        'cov_a_co2_kg',
        'perdidas_totales_cov_kg',
        'sumatoria_variacion_gl',
        'variacion_eds_gl',
        'ventas_diarias_gl',
        'perdidas_respiracion_kg',
        'variacion_formula_gl_1',
        'perdidas_operacion_kg',
        'variacion_formula_gl_2',
    ];

    private function norm(string $s): string
    {
        $s = mb_strtolower(trim($s));
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);      // quita tildes/símbolos
        $s = preg_replace('/[^a-z0-9]+/i', ' ', $s);             // deja letras/números/espacios
        return trim(preg_replace('/\s+/', ' ', $s));
    }

    private function cellValue(int $rowNumber, array $aliases)
    {
        foreach ($aliases as $alias) {
            $key = $this->norm($alias);
            if (!isset($this->headerIndex[$key])) continue;
            $col = $this->headerIndex[$key];
            // ¡Obtiene el valor calculado! (soporta referencias a otras hojas del mismo libro)
            return $this->sheet->getCellByColumnAndRow($col, $rowNumber)->getCalculatedValue();
        }
        return null;
    }

    private function toFloat($value): ?float
    {
        if ($value === null) return null;
        $s = trim((string)$value);
        if ($s === '') return null;
        if (!preg_match('/-?[\d.,]+/', $s, $m)) return null;
        $n = $m[0];
        $dot = strrpos($n, '.');
        $comma = strrpos($n, ',');
        if ($comma !== false && $comma > $dot) {
            $n = str_replace('.', '', $n);
            $n = str_replace(',', '.', $n);
        } else {
            $n = str_replace(',', '', $n);
        }
        return is_numeric($n) ? (float)$n : null;
    }

    private function parseExcelDate($value): ?string
    {
        if ($value === null || $value === '') return null;
        if (is_numeric($value)) return Carbon::instance(XlsDate::excelToDateTimeObject((float)$value))->format('Y-m-d');
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseExcelTime($value): ?string
    {
        if ($value === null || $value === '') return null;
        if (is_numeric($value)) {
            $seconds = (int) round(((float)$value) * 86400);
            return gmdate('H:i:s', $seconds);
        }
        try {
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function readNumberWithCarry(int $rowNumber, array $aliases, string $fieldKey): ?float
    {
        $v = $this->toFloat($this->cellValue($rowNumber, $aliases));
        if ($v !== null) {
            $this->carry[$fieldKey] = $v;
            return $v;
        }
        if (in_array($fieldKey, $this->carryFields, true) && array_key_exists($fieldKey, $this->carry)) {
            return $this->carry[$fieldKey];
        }
        return null;
    }

    /** ====== Procesa cada fila ====== */
    public function onRow(Row $row): void
    {
        $r = $row->getIndex();              // índice real en la hoja (1-based)
        if ($r <= $this->headingRow) return; // salta fila de encabezado

        // Fecha y hora
        $fecha = $this->parseExcelDate($this->cellValue($r, ['fecha']));
        $hora  = $this->parseExcelTime($this->cellValue($r, ['hora']));
        if (!$fecha || !$hora) return; // descarta filas incompletas

        // Humedad (detecta % o fracción)
        $humRaw = $this->cellValue($r, ['humedad ambiente']);
        $hum    = $this->toFloat($humRaw);
        if ($hum !== null) {
            $hasPercent = is_string($humRaw) && strpos($humRaw, '%') !== false;
            if ($hasPercent || $hum > 1) $hum = $hum / 100.0;
        }

        // Construye payload con alias tolerantes
        $data = [
            'temperatura_ambiente_c'   => $this->readNumberWithCarry($r, ['temperatura ambiente (°c)', 'temperatura ambiente (c)', 'temperatura ambiente c'], 'temperatura_ambiente_c'),
            'velocidad_viento'         => $this->readNumberWithCarry($r, ['velocidad del viento', 'velocidad viento'], 'velocidad_viento'),
            'humedad_ambiente'         => $hum,

            'temperatura_interna_c'    => $this->readNumberWithCarry($r, ['temperatura interna (°c)', 'temperatura interna (c)', 'temperatura interna c'], 'temperatura_interna_c'),
            'temperatura_interna_k'    => $this->readNumberWithCarry($r, ['temperatura interna kelvin', 'temperatura interna k'], 'temperatura_interna_k'),

            'volumen_gl'               => $this->readNumberWithCarry($r, ['volumen (gl)', 'volumen gl'], 'volumen_gl'),
            'descargue_combustible_gl' => $this->readNumberWithCarry($r, ['descargue combustible'], 'descargue_combustible_gl'),
            'ventas_diarias_gl'        => $this->readNumberWithCarry($r, ['ventas diarias (gl)', 'ventas diarias gl'], 'ventas_diarias_gl'),
            'diametro_tanque_in'       => $this->readNumberWithCarry($r, ['diámetro del tanque (in)', 'diametro del tanque (in)', 'diametro tanque in'], 'diametro_tanque_in'),

            'presion_hidrostatica_pa'  => $this->readNumberWithCarry($r, ['presión hidrostática p=p*g*h (pa)', 'presion hidrostática p=p*g*h (pa)', 'presion hidrostática (pa)', 'presion hidrostatica pa'], 'presion_hidrostatica_pa'),
            'presion_hidrostatica_kpa' => $this->readNumberWithCarry($r, ['presión hidrostática (kpa)', 'presion hidrostática (kpa)', 'presion hidrostatica kpa'], 'presion_hidrostatica_kpa'),
            'presion_psi' => $this->readNumberWithCarry($r, [
                'presión psi',
                'presion psi',
                'presión por libra cuadrada (psi)',
                'presion por libra cuadrada (psi)',
                'presión por libra cuadrada psi',
                'presion por libra cuadrada psi'
            ], 'presion_psi'),

            'perdidas_respiracion_kg' => $this->readNumberWithCarry($r, [
                'pérdidas por respiración del tanque (kg/día)',
                'perdidas por respiracion del tanque (kg/dia)',
                'emisión de vapor (kg/día) - respiración cov',
                'emision de vapor (kg/dia) - respiracion cov'
            ], 'perdidas_respiracion_kg'),

            'variacion_formula_gl_1' => $this->readNumberWithCarry($r, [
                'variación según fórmula 1 (gl)',
                'variacion segun formula 1 (gl)',
                'valor faltante o sobrante diario (gl) - según fórmula',
                'valor faltante o sobrante diario (gl) - segun formula'
            ], 'variacion_formula_gl_1'),

            'perdidas_operacion_kg' => $this->readNumberWithCarry($r, [
                'pérdidas por operación del tanque (kg/día)',
                'perdidas por operacion del tanque (kg/dia)',
                'emisión de vapor (kg/día) - trabajo en el tanque cov',
                'emision de vapor (kg/dia) - trabajo en el tanque cov'
            ], 'perdidas_operacion_kg'),

            'variacion_formula_gl_2' => $this->readNumberWithCarry($r, [
                'variación según fórmula 2 (gl)',
                'variacion segun formula 2 (gl)',
                'valor faltante o sobrante diario (gl) 2 - según fórmula',
                'valor faltante o sobrante diario (gl) 2 - segun formula'
            ], 'variacion_formula_gl_2'),


            'perdidas_totales_cov_kg'  => $this->readNumberWithCarry($r, ['pérdidas totales de emisión de vapor (kg/día) cov', 'perdidas totales de emision de vapor (kg/dia) cov'], 'perdidas_totales_cov_kg'),
            'cov_a_co2_kg'             => $this->readNumberWithCarry($r, ['cov convertir a kg de co2', 'cov a co2 kg'], 'cov_a_co2_kg'),

            'sumatoria_variacion_gl'   => $this->readNumberWithCarry($r, ['sumatoria valor faltante o sobrante diario (gl) - según fórmulas', 'sumatoria valor faltante o sobrante diario (gl) - segun formulas'], 'sumatoria_variacion_gl'),
            'variacion_eds_gl'         => $this->readNumberWithCarry($r, ['valor faltante o sobrante diario (gl) - según eds', 'valor faltante o sobrante diario (gl) - segun eds'], 'variacion_eds_gl'),

            'presion_sat_octano_mmhg'  => $this->readNumberWithCarry($r, ['presión de sat de octano mmhg', 'presion de sat de octano mmhg'], 'presion_sat_octano_mmhg'),
            'presion_sat_heptano_mmhg' => $this->readNumberWithCarry($r, ['presión de sat de n- heptano', 'presion de sat de n- heptano'], 'presion_sat_heptano_mmhg'),
            'presion_sat_tolueno_mmhg' => $this->readNumberWithCarry($r, ['presión de sat de tolueno', 'presion de sat de tolueno'], 'presion_sat_tolueno_mmhg'),
        ];

        // UPSERT por (estacion_id, fecha, hora)
        DatosHistoricos::updateOrCreate(
            ['estacion_id' => $this->estacionId, 'fecha' => $fecha, 'hora' => $hora],
            $data
        );
    }
}
