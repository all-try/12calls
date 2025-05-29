<?php

namespace Database\Factories;

use App\Models\Barrio;
use App\Models\Contacto;
use App\Models\User; // Para los asesores
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contacto>
 */
class ContactoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contacto::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tiposCliente = [
            'INTERESA', 'NO_INTERESA', 'TIENE_TECNICO', 'FUERA_DE_MEDELLIN',
            'MALO', 'INACTIVO', 'INVITAR_NUEVAMENTE', 'REPROGRAMAR', // <--- ¡AQUÍ ESTÁ EL PROBLEMA!
            'VENTA', 'INICIAL', 'NUMERO_EQUIVOCADO', 'NO_VOLVER_A_LLAMAR'
        ];

        // Asegúrate de que existan barrios y asesores, o créalos si no.
        // Para id_barrio
        $barrioId = null;
        if (Barrio::count() > 0) {
            $barrioId = Barrio::inRandomOrder()->first()->id;
        } else {
            // Si no hay barrios, puedes crear uno de ejemplo o dejarlo null si tu FK lo permite
            // $barrioId = Barrio::factory()->create()->id; // Opcional: crear barrio si no existe
        }

        // Para id_asesor
        $asesorId = null;
        // Asume que los asesores son usuarios con rol 'ASESOR' o 'ADMIN'
        $posiblesAsesores = User::whereIn('rol', ['ASESOR', 'ADMIN', 'SUPER_ADMIN'])->where('activo', true)->get();
        if ($posiblesAsesores->count() > 0) {
            $asesorId = $posiblesAsesores->random()->id;
        } else {
            // Si no hay asesores, puedes crear uno o dejarlo null si la FK lo permite
            // $asesorId = User::factory()->create(['rol' => 'ASESOR'])->id; // Opcional
        }


        return [
            'cedula' => $this->faker->unique()->optional(0.7)->numerify('##########'), // 70% de probabilidad de tener cédula
            'nombre_completo' => $this->faker->name,
            'direccion' => $this->faker->streetAddress, // Faker podría necesitar configuración para direcciones colombianas
            'id_barrio' => $barrioId,
            'celular' => $this->faker->numerify('3##########'), // Siempre un celular
            'telefono_fijo' => $this->faker->optional(0.3)->numerify('604#######'), // 30% de tener fijo (Medellín)
            'telefono_opcional' => $this->faker->optional(0.1)->numerify('3#########'),
            'tipo_cliente' => $this->faker->randomElement($tiposCliente),
            'id_asesor' => $asesorId,
            'observaciones_cliente' => $this->faker->optional(0.5)->sentence(10), // 50% de tener observaciones
        ];
    }
}