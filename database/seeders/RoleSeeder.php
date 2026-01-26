<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'nombre' => 'Súper Administrador',
            'slug' => 'admin',
            'descripcion' => 'Rol con acceso total al sistema',
        ]);

        Role::create([
            'nombre' => 'Líder',
            'slug' => 'lider',
            'descripcion' => 'Rol de lider',
        ]);

        Role::create([
            'nombre' => 'Testigo',
            'slug' => 'testigo',
            'descripcion' => 'Rol de testigo',
        ]);

        Role::create([
            'nombre' => 'Administrador de campaña',
            'slug' => 'campana',
            'descripcion' => 'Rol de administrador de campaña',
        ]);
    }
}
