<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lider extends Model
{
    //
    protected $table = 'lideres';

    public $timestamps = false;

    protected $fillable = [
        'persona_id'
    ];

    public function Persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }

    public function votantes()
    {
        return $this->hasMany(Votante::class, 'lider_id', 'id');
    }

    public function getLiderIdAttribute()
    {
        return $this->attributes['id'];
    }

    public function getIdAttribute()
    {
        return $this->persona_id;
    }

    public function getNombreAttribute()
    {
        return $this->persona->nombre;
    }

    public function getCedulaAttribute()
    {
        return $this->persona->cedula;
    }

    public function getTelefonoAttribute()
    {
        return $this->persona->telefono;
    }

    public function getCorregimientoAttribute()
    {
        return $this->persona->corregimiento;
    }

    public function getCorregimientoIdAttribute()
    {
        return $this->persona->corregimiento_id;
    }

    public function getBarrioAttribute()
    {
        return $this->persona->barrio;
    }

    public function getBarrioIdAttribute()
    {
        return $this->persona->barrio_id;
    }

    public function getMesaAttribute()
    {
        return $this->persona->mesa;
    }

    public function getMesaIdAttribute()
    {
        return $this->persona->mesa_id;
    }

    public function getGeneroAttribute()
    {
        return $this->persona->genero;
    }

    public function getGeneroIdAttribute()
    {
        return $this->persona->genero_id;
    }
}
