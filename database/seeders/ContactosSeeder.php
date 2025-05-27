<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Contacto;

class ContactosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Solo crear contactos si existen barrios y asesores para evitar errores de FK
        if (\App\Models\Barrio::count() === 0) {
            $this->command->warn('No hay barrios en la base de datos. Ejecuta BarriosMedellinSeeder primero. Saltando ContactosSeeder.');
            return;
        }
        if (\App\Models\User::whereIn('rol', ['ASESOR', 'ADMIN', 'SUPER_ADMIN'])->where('activo', true)->count() === 0) {
            $this->command->warn('No hay usuarios (asesores/admins) activos. Crea algunos primero o ajusta UserSeeder. Saltando ContactosSeeder.');
            return;
        }

        Contacto::factory()->count(10)->create();
    }
}