<?php
// app/Models/DatosHistoricos.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatosHistoricos extends Model
{
    protected $table = 'datos_historicos';

    protected $fillable = [
        'estacion_id','fecha','hora',
        'temperatura_ambiente_c','velocidad_viento','humedad_ambiente',
        'temperatura_interna_c','temperatura_interna_k',
        'volumen_gl','descargue_combustible_gl','ventas_diarias_gl',
        'diametro_tanque_in','presion_hidrostatica_pa','presion_hidrostatica_kpa',
        'presion_psi','perdidas_respiracion_kg','variacion_formula_gl_1',
        'perdidas_operacion_kg','variacion_formula_gl_2','perdidas_totales_cov_kg',
        'cov_a_co2_kg','sumatoria_variacion_gl','variacion_eds_gl',
        'presion_sat_octano_mmhg','presion_sat_heptano_mmhg','presion_sat_tolueno_mmhg',
    ];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
    ];

    public function estacion()
    {
        return $this->belongsTo(Estacion::class, 'estacion_id');
    }
}
