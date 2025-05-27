<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash; // Asegúrate de tener esto si vas a hashear contraseñas aquí

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear tu usuario principal (el que usas para Filament)
        if (User::where('email', 'santiago@gmail.com')->doesntExist()) {
            User::factory()->create([
                'name' => 'Santiago',
                'email' => 'santiago@gmail.com',
                'password' => Hash::make('santiago'), // ¡Hashea la contraseña!
                'rol' => 'SUPER_ADMIN', // O el rol que deba tener
                'activo' => true,
                // 'email_verified_at' => now(), // Si quieres que esté verificado
            ]);
        }

        // 2. Crear otros usuarios base para pruebas
        if (User::where('email', 'maribel@gmail.com')->doesntExist()) {
            User::factory()->create([
                'name' => 'Maribel',
                'email' => 'maribel@gmail.com',
                'rol' => 'SUPER_ADMIN',
                'activo' => true,
            ]);
        }

        if (User::where('email', 'yohana@gmail.com')->doesntExist()) {
            User::factory()->create([
                'name' => 'Yohana',
                'email' => 'yohana@gmail.com',
                'rol' => 'ASESOR',
                'activo' => true,
            ]);
        }

        // 3. Llama a tus Seeders personalizados
        $this->call([
            BarriosMedellinSeeder::class,
            TecnicosSeeder::class,
            ContactosSeeder::class,
            // ElectrodomesticosSeeder::class, // Si lo tienes
        ]);
    }
}