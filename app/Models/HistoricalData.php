<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricalData extends Model
{
    use HasFactory;

    protected $fillable = [
        'station',
        'record_date',
        'record_time',
        'ambient_temp_c',
        'wind_speed',
        'ambient_humidity',
        'internal_temp_c',
        'internal_temp_k',
        'volume_gl',
        'fuel_discharge_gl',
        'daily_sales_gl',
        'tank_diameter_in',
        'hydrostatic_pressure_pa',
        'hydrostatic_pressure_kpa',
        'pressure_psi',
        'voc_breathing_emissions_kg',
        'formula_variance_gl_1',
        'voc_working_emissions_kg',
        'formula_variance_gl_2',
        'total_voc_emissions_kg',
        'voc_to_co2_kg',
        'total_variance_gl',
        'station_variance_gl',
        'octane_sat_pressure_mmhg',
        'heptane_sat_pressure_mmhg',
        'toluene_sat_pressure_mmhg',
    ];
}
