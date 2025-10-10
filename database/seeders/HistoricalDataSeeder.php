<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HistoricalData;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class HistoricalDataSeeder extends Seeder
{
    /**
     * Función de limpieza robusta para convertir diferentes formatos de texto a un número flotante.
     * Maneja separadores de miles (puntos o comas) y un separador decimal (punto o coma).
     */
    private function cleanNumericValue($value)
    {
        if (is_null($value) || trim((string) $value) === '') {
            return null;
        }

        $value = (string) $value;

        // Extrae la parte numérica, manteniendo puntos y comas
        preg_match('/-?[\d.,]+/', $value, $matches);
        if (!isset($matches[0])) {
            return null; // No se encontró ningún número
        }
        $numberString = $matches[0];

        $lastDotPos = strrpos($numberString, '.');
        $lastCommaPos = strrpos($numberString, ',');

        // Determina cuál es el separador decimal basado en la última aparición
        if ($lastCommaPos !== false && $lastCommaPos > $lastDotPos) {
            // La coma es el decimal (formato: 1.234,56)
            $numberString = str_replace('.', '', $numberString); // Elimina puntos de miles
            $numberString = str_replace(',', '.', $numberString); // Reemplaza coma decimal por punto
        } else {
            // El punto es el decimal (formato: 1,234.56)
            $numberString = str_replace(',', '', $numberString); // Elimina comas de miles
        }

        return is_numeric($numberString) ? (float) $numberString : null;
    }

    public function run(): void
    {
        HistoricalData::truncate();
        $filePath = database_path('data/test_datos.xlsx');
        $sheetNames = ['Cota test', 'Ubaté test', 'Silvania test'];

        foreach ($sheetNames as $index => $sheetName) {
            $this->command->info("Procesando hoja: {$sheetName}");
            $sheetData = Excel::toCollection(null, $filePath)[$index];

            // ... (extracción de celdas combinadas) ...
            $daily_sales_gl = $this->cleanNumericValue($sheetData[1][10] ?? null);
            // ... (resto de las celdas combinadas) ...

            for ($i = 1; $i <= 24; $i++) {
                $row = $sheetData[$i] ?? null;
                if (empty($row) || empty($row[1]) || !is_numeric($row[1])) {
                    continue;
                }

                // AJUSTE ESPECIAL PARA LA HUMEDAD CON PORCENTAJE
                $humidity = $this->cleanNumericValue($row[5] ?? null);
                if (strpos((string)($row[5] ?? ''), '%') !== false) {
                    $humidity = $humidity / 100;
                }

                HistoricalData::create([
                    'station' => $sheetName,
                    'record_date' => Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[1])),
                    'record_time' => gmdate("H:i:s", (int)($this->cleanNumericValue($row[2] ?? null) * 86400)),
                    
                    // Todos los campos ahora usan la nueva función de limpieza
                    'ambient_temp_c' => $this->cleanNumericValue($row[3] ?? null),
                    'wind_speed' => $this->cleanNumericValue($row[4] ?? null),
                    'ambient_humidity' => $humidity, // <-- Usamos la variable ajustada
                    'internal_temp_c' => $this->cleanNumericValue($row[6] ?? null),
                    'internal_temp_k' => $this->cleanNumericValue($row[7] ?? null),
                    'volume_gl' => $this->cleanNumericValue($row[8] ?? null),
                    'fuel_discharge_gl' => $this->cleanNumericValue($row[9] ?? null),
                    'tank_diameter_in' => $this->cleanNumericValue($row[11] ?? null),
                    'hydrostatic_pressure_pa' => $this->cleanNumericValue($row[12] ?? null),
                    'hydrostatic_pressure_kpa' => $this->cleanNumericValue($row[13] ?? null),
                    'pressure_psi' => $this->cleanNumericValue($row[14] ?? null),
                    'voc_breathing_emissions_kg' => $this->cleanNumericValue($row[15] ?? null),
                    'formula_variance_gl_1' => $this->cleanNumericValue($row[16] ?? null),
                    'voc_working_emissions_kg' => $this->cleanNumericValue($row[17] ?? null),
                    'formula_variance_gl_2' => $this->cleanNumericValue($row[18] ?? null),
                    'total_voc_emissions_kg' => $this->cleanNumericValue($row[19] ?? null),
                    'voc_to_co2_kg' => $this->cleanNumericValue($row[20] ?? null),
                    'total_variance_gl' => $this->cleanNumericValue($row[21] ?? null),
                    'station_variance_gl' => $this->cleanNumericValue($row[22] ?? null),
                    'octane_sat_pressure_mmhg' => $this->cleanNumericValue($row[23] ?? null),
                    'heptane_sat_pressure_mmhg' => $this->cleanNumericValue($row[24] ?? null),
                    'toluene_sat_pressure_mmhg' => $this->cleanNumericValue($row[25] ?? null),
                    'daily_sales_gl' => $daily_sales_gl,
                ]);
            }
        }
        $this->command->info('¡Datos importados y formateados exitosamente!');
    }
}