<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testigo extends Model
{
    //
    protected $table = 'testigo';
    protected $fillable = [
        'mesa_id',
        'cantidad_votos',
        'observaciones',
        'archivo',
        'usuario'
    ];

    public $timestamps = false;

    public function mesa()
    {
        return $this->belongsTo(Mesa::class, 'mesa_id');
    }

    public function reporteVotosCandidatos()
    {
        return $this->hasMany(ReporteVotoCandidato::class, 'testigo_id');
    }

    public function candidatos()
    {
        return $this->belongsToMany(Candidato::class, 'reporte_votos_candidatos', 'testigo_id', 'candidato_id')
            ->withPivot('votos')
            ->withTimestamps();
    }
}
