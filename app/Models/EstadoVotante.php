<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstadoVotante extends Model
{
    protected $table = 'estados_votante';

    protected $fillable = [
        'nombre',
        'color',
        'icono',
        'orden',
    ];

    public function personas()
    {
        return $this->hasMany(Persona::class, 'estado_votante_id');
    }

    /**
     * Obtener todos los estados ordenados
     */
    public static function getOrdenados()
    {
        return self::orderBy('orden')->get();
    }

    /**
     * Obtener el estado "Comprometido"
     */
    public static function getComprometido()
    {
        return self::where('nombre', 'Comprometido')->first();
    }

    /**
     * Obtener conteo de personas por estado
     */
    public static function getConteosPorEstado()
    {
        return self::withCount('personas')
            ->orderBy('orden')
            ->get();
    }
}
