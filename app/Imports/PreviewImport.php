<?php

// app/Imports/PreviewImport.php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class PreviewImport implements ToArray, WithHeadingRow, WithCalculatedFormulas
{
    public function __construct(private int $limit = 25) {}
    public function array(array $rows): array { return array_slice($rows, 0, $this->limit); }
}

