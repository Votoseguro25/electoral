<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Puesto extends Model
{
    protected $table = 'puestos';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'direccion',
        'municipio_id',
        'cantidad_hombres',
        'cantidad_mujeres',
        'altitud',
        'longitud',
        'cantidad_mesas',
    ];

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'municipio_id', 'id');
    }

    public function mesas()
    {
        return $this->hasMany(Mesa::class, 'puesto_id', 'id');
    }    
}
