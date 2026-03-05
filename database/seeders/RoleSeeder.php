<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role; // <-- IMPORTACIÓN DE LA LIBRERÍA
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // 1. Creamos el rol de administrador usando la librería
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // 2. Creamos tu usuario de pruebas
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@flick.com'], // Usa tu correo aquí
            [
                'name' => 'Admin',
                'password' => Hash::make('123456789'), // Clave temporal: password
            ]
        );

        // 3. Le asignamos el rol usando el método mágico de la librería
        $adminUser->assignRole($adminRole); 
    }
}