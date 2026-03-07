<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reportare14 extends Model
{
    protected $table = 'reportare14';
    protected $primaryKey = 'ID';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'E14_ID',
        "Formulario_Identificacion",
        'DEPARTAMENTO',
        'MUNICIPIO',
        'PUESTO',
        'MESA',
        'TOTAL_VOTANTES-E11',
        'TOTAL_VOTOS-URNA',
        'VOTOS_BLANCO',
        'VOTOS_NULOS',
        'SUMA_VOTOS-E14',
        'OBSERVACION',
        'ARCHIVO',
        'Estado',
        'NOVEDAD',
        'TESTIGO',
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class, 'DEPARTAMENTO', 'id');
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class, 'MUNICIPIO', 'id');
    }

    public function puesto()
    {
        return $this->belongsTo(Puesto::class, 'PUESTO', 'id');
    }

    public function testigo()
    {
        return $this->belongsTo(Testigo::class, 'TESTIGO', 'id');
    }
}
