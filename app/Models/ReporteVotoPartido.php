<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReporteVotoPartido extends Model
{

    use HasFactory;

    protected $table = 'reporte_votos_camara';

    protected $primaryKey = 'ReportCam_ID';

    protected $keyType = 'int';       
    public $incrementing = true;       
    public $timestamps = false;

    protected $fillable = [
        'Partido',
        'e14',
        'votos',
    ];

    /**
     * Relación con el modelo Partido 
     * (Asumiendo que tienes una tabla 'partido')
     */
    public function partido()
    {
        return $this->belongsTo(Partido::class, 'Partido', 'id');
    }

    /**
     * Relación con el modelo E14 
     * (Asumiendo que la columna e14 es una llave foránea)
     */
    public function e14()
    {
        return $this->belongsTo(E14::class, 'e14', 'id');
    }
}