<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Votante extends Model
{
    //    
    protected $table = 'votantes';

    public $timestamps = false;

    protected $fillable = [
        'persona_id',
        'compromiso_id',
        'lider_id',
    ];

    public function compromiso()
    {
        return $this->belongsTo(Compromiso::class, 'compromiso_id', 'id');
    }

    public function lider()
    {
        return $this->belongsTo(Lider::class, 'lider_id', 'id');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'persona_id', 'id');
    }

    // Accessors para acceder a los datos de la persona
    public function getNombreAttribute()
    {
        return $this->persona?->nombre;
    }

    public function getCedulaAttribute()
    {
        return $this->persona?->cedula;
    }

    public function getTelefonoAttribute()
    {
        return $this->persona?->telefono;
    }

    public function getProfesionAttribute()
    {
        return $this->persona?->profesion;
    }
    
    public function getMunicipioAttribute()
    {
        return $this->persona?->municipio;
    }

    public function getMunicipioIdAttribute()
    {
        return $this->persona?->municipio_id;
    }

    public function getMesaAttribute()
    {
        return $this->persona?->mesa;
    }

    public function getMesaIdAttribute()
    {
        return $this->persona?->mesa_id;
    }

    public function getGeneroAttribute()
    {
        return $this->persona?->genero;
    }

    public function getGeneroIdAttribute()
    {
        return $this->persona?->genero_id;
    }

    public function getDepartamentoIdAttribute()
    {
        return $this->persona?->municipio?->departamento_id;
    }
}
