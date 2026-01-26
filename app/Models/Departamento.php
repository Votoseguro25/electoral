<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Departamento extends Model
{
    //
    protected $table = 'departamentos';

    protected $fillable = ['nombre'];

    public $timestamps = false;

    public function municipios()
    {
        return $this->hasMany(Municipio::class, 'departamento_id', 'id');
    }

}
