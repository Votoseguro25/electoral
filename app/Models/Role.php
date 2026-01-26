<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'slug',
        'descripcion',
    ];

    /**
     * Relación: Un rol puede tener muchos usuarios
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }
}
