<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEleccion extends Model
{
    protected $table = 'tipo_eleccion';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
    ];

    public function campanas()
    {
        return $this->hasMany(Campana::class, 'tipo_eleccion_id', 'id');
    }
}
