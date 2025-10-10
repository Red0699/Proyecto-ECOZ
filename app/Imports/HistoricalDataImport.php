<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas; // <-- Importa esto

// Añade WithCalculatedFormulas aquí
class HistoricalDataImport implements ToCollection, WithCalculatedFormulas
{
    public function collection(Collection $rows)
    {
        // No necesitamos hacer nada aquí,
        // solo queremos que la clase exista para aplicar la configuración.
    }
}