<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Normativa extends Model
{
    protected $fillable = [
        'codigo', 'titulo', 'reglamentacion', 'sanciones', 'estacion_id'
    ];

    public function estacion()
    {
        return $this->belongsTo(Estacion::class);
    }
}
