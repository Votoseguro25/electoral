<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partido extends Model
{
    //
    protected $table = 'partido';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'estado'
    ];

    public function candidatos()
    {
        return $this->hasMany(Candidato::class, 'partido_id', 'id');
    }
}
