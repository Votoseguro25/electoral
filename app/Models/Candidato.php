<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Candidato extends Model
{
    //
    protected $table = 'candidatos';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'Tarjeton',
        'foto',
        'color',
        'partido_id',
    ];

    public function partido()
    {
        return $this->belongsTo(Partido::class, 'partido_id', 'id');
    }

    public function campanas()
    {
        return $this->hasMany(Campana::class, 'candidato_id', 'id');
    }

    public function reporteVotosCandidatos()
    {
        return $this->hasMany(ReporteVotoCandidato::class, 'candidato_id');
    }

    public function testigos()
    {
        return $this->belongsToMany(Testigo::class, 'reporte_votos_candidatos', 'candidato_id', 'testigo_id')
            ->withPivot('votos')
            ->withTimestamps();
    }
}
