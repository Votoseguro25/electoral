<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campana extends Model
{
    protected $table = 'campana';

    public $timestamps = false;

    protected $fillable = [
        'tipo_eleccion_id',
        'municipio_id',
        'candidato_id',
        'departamento_id',
    ];

    public function tipoEleccion()
    {
        return $this->belongsTo(TipoEleccion::class, 'tipo_eleccion_id', 'id');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'municipio_id', 'id');
    }

    public function candidato()
    {
        return $this->belongsTo(Candidato::class, 'candidato_id', 'id');
    }

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'departamento_id', 'id');
    }
}
