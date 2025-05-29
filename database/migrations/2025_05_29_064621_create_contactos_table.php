<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contactos', function (Blueprint $table) {
            $table->id();
            $table->string('cedula', 20)->unique()->nullable();
            $table->string('nombre_completo', 100);
            $table->string('direccion', 150);
            $table->foreignId('id_barrio')->nullable()->constrained('barrios')->onDelete('set null');
            $table->string('telefono_fijo', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('telefono_opcional', 20)->nullable();
            $table->enum('tipo_cliente', [
                'INTERESA', 'NO_INTERESA', 'TIENE_TECNICO', 'FUERA_DE_MEDELLIN',
                'MALO', 'INACTIVO', 'INVITAR_NUEVAMENTE', 'VENTA', 'INICIAL',
                'NUMERO_EQUIVOCADO', 'NO_VOLVER_A_LLAMAR' // Estados añadidos/revisados
            ])->default('INICIAL');
            $table->foreignId('id_asesor')->nullable()->constrained('users')->onDelete('set null');
            $table->text('observaciones_cliente')->nullable();
            $table->timestamps();

            $table->index('nombre_completo');
            $table->index('celular');
            $table->index('tipo_cliente');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contactos');
    }
};