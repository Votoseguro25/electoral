<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    protected $table = 'personas';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'cedula',
        'telefono',
        'profesion',
        'mesa_id',
        'genero_id',
        'reporte_voto',
        'municipio_id',
        'creador_id'
    ];

    public function lider()
    {
        return $this->hasOne(Lider::class, 'persona_id', 'id');
    }

    public function votante()
    {
        return $this->hasOne(Votante::class, 'persona_id', 'id');
    }

    public function genero()
    {
        return $this->belongsTo(Genero::class, 'genero_id', 'id');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'municipio_id', 'id');
    }

    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'mesa_id', 'id');
    }
}
