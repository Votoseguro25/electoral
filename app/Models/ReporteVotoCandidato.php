<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReporteVotoCandidato extends Model
{
    //
    protected $table = 'reporte_votos_candidatos';

    protected $fillable = [
        'testigo_id',
        'candidato_id',
        'votos',
        'e14'
    ];

    public function testigo()
    {
        return $this->belongsTo(Testigo::class, 'testigo_id');
    }

    public function candidato()
    {
        return $this->belongsTo(Candidato::class, 'candidato_id');
    }
    
    public function e14()
    {
        return $this->belongsto(Reportare14::class, 'e14');
    }
}
