<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteCandidatosVotosCamara extends Model
{
    use HasFactory;

    protected $table = 'Votos_candidatosCam';

    protected $primaryKey = 'ID'; // si tu PK es ID y no id

    public $timestamps = false;

    protected $fillable = [
        'candidato',
        'e14',
        'votos'
    ];

    // 🔗 Relacion con candidatos
    public function candidato()
    {
        return $this->belongsTo(Candidato::class, 'candidato', 'id');
    }

    // 🔗 Relacion con reporte camara (E14)
    public function reporteCamara()
    {
        return $this->belongsTo(Reportare14::class, 'e14', 'ID');
    }
}
