<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DataImport;

class Estacion extends Model
{
    protected $table = 'estaciones'; 
    protected $fillable = ['nombre'];
    public function datos()
    {
        return $this->hasMany(DatosHistoricos::class);
    }
    public function lotes()
    {
        return $this->hasMany(DataImport::class);
    }
}
