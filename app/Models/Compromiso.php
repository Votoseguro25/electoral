<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compromiso extends Model
{
    protected $table = 'compromisos';

    protected $fillable = [
        'nombre',
    ];

    public $timestamps = false;

    public function votantes()
    {
        return $this->hasMany(Persona::class, 'compromiso_id', 'id');
    }
}