<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Barrio; // O DB::table('barrios')->insert([...]) para más rendimiento si son muchísimos

class BarriosMedellinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $barrios = [
            // Comuna 1 - Popular
            ['nombre' => 'Popular N.º 1', 'zona' => 'Nororiental'],
            ['nombre' => 'Santo Domingo Savio N.º 1', 'zona' => 'Nororiental'],
            ['nombre' => 'Granizal', 'zona' => 'Nororiental'],
            ['nombre' => 'Moscú N.º 1', 'zona' => 'Nororiental'],
            ['nombre' => 'Villa Guadalupe', 'zona' => 'Nororiental'],
            // Comuna 2 - Santa Cruz
            ['nombre' => 'Santa Cruz', 'zona' => 'Nororiental'],
            ['nombre' => 'La Rosa', 'zona' => 'Nororiental'],
            ['nombre' => 'La Isla', 'zona' => 'Nororiental'],
            ['nombre' => 'Villa Niza', 'zona' => 'Nororiental'],
            // Comuna 3 - Manrique
            ['nombre' => 'Manrique Central N.º 1', 'zona' => 'Nororiental'],
            ['nombre' => 'Manrique Central N.º 2', 'zona' => 'Nororiental'],
            ['nombre' => 'El Pomar', 'zona' => 'Nororiental'],
            ['nombre' => 'Las Granjas', 'zona' => 'Nororiental'],
            // Comuna 4 - Aranjuez
            ['nombre' => 'Aranjuez', 'zona' => 'Nororiental'],
            ['nombre' => 'Berlín', 'zona' => 'Nororiental'],
            ['nombre' => 'San Isidro', 'zona' => 'Nororiental'],
            ['nombre' => 'Campo Valdés N.º 1', 'zona' => 'Nororiental'],
            // Comuna 5 - Castilla
            ['nombre' => 'Castilla', 'zona' => 'Noroccidental'],
            ['nombre' => 'Florencia', 'zona' => 'Noroccidental'],
            ['nombre' => 'Alfonso López', 'zona' => 'Noroccidental'],
            ['nombre' => 'Boyacá', 'zona' => 'Noroccidental'],
            // Comuna 6 - Doce de Octubre
            ['nombre' => 'Doce de Octubre N.º 1', 'zona' => 'Noroccidental'],
            ['nombre' => 'Doce de Octubre N.º 2', 'zona' => 'Noroccidental'],
            ['nombre' => 'Picacho', 'zona' => 'Noroccidental'],
            ['nombre' => 'Pedregal', 'zona' => 'Noroccidental'],
            // Comuna 7 - Robledo
            ['nombre' => 'Robledo', 'zona' => 'Noroccidental'],
            ['nombre' => 'Aures N.º 1', 'zona' => 'Noroccidental'],
            ['nombre' => 'Aures N.º 2', 'zona' => 'Noroccidental'],
            ['nombre' => 'Villa Flora', 'zona' => 'Noroccidental'],
            // Comuna 8 - Villa Hermosa
            ['nombre' => 'Villa Hermosa', 'zona' => 'Centroriental'],
            ['nombre' => 'La Mansión', 'zona' => 'Centroriental'],
            ['nombre' => 'Manrique Oriental', 'zona' => 'Centroriental'],
            ['nombre' => 'El Jardín', 'zona' => 'Centroriental'],
            // Comuna 9 - Buenos Aires
            ['nombre' => 'Buenos Aires', 'zona' => 'Centroriental'],
            ['nombre' => 'Miraflores', 'zona' => 'Centroriental'],
            ['nombre' => 'Cataluña', 'zona' => 'Centroriental'],
            ['nombre' => 'Barrios de Jesús', 'zona' => 'Centroriental'],
            // Comuna 10 - La Candelaria (Centro)
            ['nombre' => 'La Candelaria', 'zona' => 'Centro'],
            ['nombre' => 'Prado', 'zona' => 'Centro'],
            ['nombre' => 'San Benito', 'zona' => 'Centro'],
            ['nombre' => 'Boston', 'zona' => 'Centro'],
            ['nombre' => 'Barrio Colón', 'zona' => 'Centro'],
            // Comuna 11 - Laureles-Estadio
            ['nombre' => 'Laureles', 'zona' => 'Centroccidental'],
            ['nombre' => 'Estadio', 'zona' => 'Centroccidental'],
            ['nombre' => 'Conquistadores', 'zona' => 'Centroccidental'],
            ['nombre' => 'Carlos E. Restrepo', 'zona' => 'Centroccidental'],
            ['nombre' => 'San Joaquín', 'zona' => 'Centroccidental'],
            // Comuna 12 - La América
            ['nombre' => 'La América', 'zona' => 'Centroccidental'],
            ['nombre' => 'La Floresta', 'zona' => 'Centroccidental'],
            ['nombre' => 'Calasanz', 'zona' => 'Centroccidental'],
            ['nombre' => 'Santa Mónica', 'zona' => 'Centroccidental'],
            // Comuna 13 - San Javier
            ['nombre' => 'San Javier N.º 1', 'zona' => 'Centroccidental'],
            ['nombre' => 'San Javier N.º 2', 'zona' => 'Centroccidental'],
            ['nombre' => 'El Salado', 'zona' => 'Centroccidental'],
            ['nombre' => 'Veinte de Julio', 'zona' => 'Centroccidental'],
            // Comuna 14 - El Poblado
            ['nombre' => 'El Poblado', 'zona' => 'Suroriental'],
            ['nombre' => 'Manila', 'zona' => 'Suroriental'],
            ['nombre' => 'Provenza', 'zona' => 'Suroriental'],
            ['nombre' => 'Astorga', 'zona' => 'Suroriental'],
            ['nombre' => 'Castropol', 'zona' => 'Suroriental'],
            // Comuna 15 - Guayabal
            ['nombre' => 'Guayabal', 'zona' => 'Suroccidental'],
            ['nombre' => 'Campo Amor', 'zona' => 'Suroccidental'],
            ['nombre' => 'Cristo Rey', 'zona' => 'Suroccidental'],
            ['nombre' => 'Santa Fe', 'zona' => 'Suroccidental'],
            // Comuna 16 - Belén
            ['nombre' => 'Belén', 'zona' => 'Suroccidental'],
            ['nombre' => 'Fátima', 'zona' => 'Suroccidental'],
            ['nombre' => 'Rosales', 'zona' => 'Suroccidental'],
            ['nombre' => 'La Mota', 'zona' => 'Suroccidental'],
            ['nombre' => 'Altavista', 'zona' => 'Suroccidental'], // También corregimiento pero tiene sector urbano
            // Corregimientos (ejemplos de algunos centros poblados)
            ['nombre' => 'San Antonio de Prado (Centro)', 'zona' => 'Corregimiento Suroccidental'],
            ['nombre' => 'San Cristóbal (Centro)', 'zona' => 'Corregimiento Noroccidental'],
            ['nombre' => 'Santa Elena (Centro)', 'zona' => 'Corregimiento Oriental'],
            ['nombre' => 'Altavista (Corregimiento)', 'zona' => 'Corregimiento Suroccidental'],
            ['nombre' => 'San Sebastián de Palmitas (Centro)', 'zona' => 'Corregimiento Occidental'],
        ];

        foreach ($barrios as $barrio) {
            Barrio::firstOrCreate(['nombre' => $barrio['nombre']], ['zona' => $barrio['zona']]);
        }

        // También puedes insertar tus ejemplos iniciales aquí si quieres
        $barriosEjemplo = [
            ['nombre' => 'El Poblado', 'zona' => 'Suroriental'], // Se repetirá si ya está, firstOrCreate lo maneja
            ['nombre' => 'Laureles', 'zona' => 'Occidental'],
            ['nombre' => 'Belén', 'zona' => 'Suroccidental'],
            ['nombre' => 'Envigado Centro', 'zona' => 'Sur (Municipio cercano)'], // Ejemplo fuera de Medellín pero común
        ];
         foreach ($barriosEjemplo as $barrio) {
            Barrio::firstOrCreate(['nombre' => $barrio['nombre']], ['zona' => $barrio['zona']]);
        }
    }
}