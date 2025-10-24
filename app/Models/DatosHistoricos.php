<?php
// app/Models/DatosHistoricos.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DataImport;

class DatosHistoricos extends Model
{
    protected $table = 'datos_historicos';

    protected $fillable = [
        'estacion_id', 
        'lote_id',
        'fecha', // Fecha 
        'hora', // Hora
        'temperatura_ambiente_c', //Temperatura ambiente (°C)
        'velocidad_viento', // Velocidad del viento
        'humedad_ambiente', // Humedad ambiente
        'temperatura_interna_c', // Temperatura interna (°C)
        'temperatura_interna_k', // Temperatura interna Kelvin
        'volumen_gl', // Volumen (gl)
        'descargue_combustible_gl', // Descargue combustible
        'ventas_diarias_gl', // Ventas diarias (gl)
        'diametro_tanque_in', // Diámetro del tanque (in)
        'presion_hidrostatica_pa', // Presión hidrostática p=p*g*h (pa)
        'presion_hidrostatica_kpa', // Presión hidrostática (kPa)
        'presion_psi', // Presión por libra cuadrada (Psi)
        'perdidas_respiracion_kg', // Emisión de vapor (kg/día) - Respiración COV
        'variacion_formula_gl_1', // Valor faltante o sobrante diario (gl) - Según fórmula
        'perdidas_operacion_kg', // Emisión de vapor (kg/día) - Trabajo en el tanque COV
        'variacion_formula_gl_2', // Valor faltante o sobrante diario (gl) - Según fórmula
        'perdidas_totales_cov_kg', // Pérdidas totales de emisión de vapor (kg/día) COV
        'cov_a_co2_kg', // COV convertir a kg de CO2
        'sumatoria_variacion_gl', // Sumatoria valor faltante o sobrante diario (gl) - Según fórmulas
        'variacion_eds_gl', // Valor faltante o sobrante diario (gl) - Según eds
        'presion_sat_octano_mmhg', // Presión de Sat de Octano MmHg
        'presion_sat_heptano_mmhg', // Presión de Sat de n- Heptano
        'presion_sat_tolueno_mmhg', // Presión de Sat de Tolueno
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
    ];

    public function estacion()
    {
        return $this->belongsTo(Estacion::class, 'estacion_id');
    }

    public function lote()
    {
        return $this->belongsTo(DataImport::class, 'lote_id');
    }
}
