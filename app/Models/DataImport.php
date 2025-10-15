<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DataImport extends Model
{
    protected $table = 'data_imports';
    protected $fillable = ['estacion_id','user_id','archivo','mime','size','path','filas','estado'];

    public function estacion() { return $this->belongsTo(Estacion::class); }
    public function user()     { return $this->belongsTo(User::class); }
    public function datos()    { return $this->hasMany(DatosHistoricos::class, 'lote_id'); }
}

