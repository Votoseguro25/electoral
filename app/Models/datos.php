<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class datos extends Model
{
    protected $table = 'DATOS_2';

    public $timestamps = false;

    protected $fillable = [
        'total',
        'departamento'
    ];

    public function tipoEleccion()
    {
        return $this->belongsTo(TipoEleccion::class, 'tipo_eleccion_id', 'id');
    }

    
}
