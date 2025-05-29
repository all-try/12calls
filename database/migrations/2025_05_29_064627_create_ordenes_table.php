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
        Schema::create('ordenes', function (Blueprint $table) {
            $table->id();
            $table->string('numero_orden', 25)->unique();
            $table->foreignId('id_contacto')->constrained('contactos')->onDelete('restrict');
            $table->foreignId('id_tecnico')->nullable()->constrained('tecnicos')->onDelete('set null');
            $table->foreignId('id_asesor_creo')->constrained('users')->onDelete('restrict');
            $table->string('direccion_servicio', 255);
            $table->foreignId('id_barrio_servicio')->nullable()->constrained('barrios')->onDelete('set null');
            $table->enum('tipo_servicio', [
                'MANTENIMIENTO', 
                'REPARACION', 
                'REVISION'
            ]);
            $table->enum('estado_orden', [
                'PENDIENTE_ASIGNAR', 'ASIGNADA', 'EN_PROCESO', 
                'COMPLETADA', // Servicio finalizado, pendiente que asesor registre liquidación
                'LIQUIDADA',  // Asesor ya registró la liquidación
                'CANCELADA', 'REPROGRAMADA_INTERNAMENTE', 'REPROGRAMADA_CLIENTE',
                'REQUIERE_COTIZACION', 
                'COTIZACION_PENDIENTE_CLIENTE', 
                'COTIZACION_APROBADA', 
                'COTIZACION_RECHAZADA'
            ])->default('PENDIENTE_ASIGNAR');
            // fecha_orden es cubierta por created_at
            $table->date('fecha_servicio_programada')->nullable();
            $table->time('hora_servicio_programada')->nullable();
            $table->dateTime('fecha_servicio_realizada')->nullable();
            $table->decimal('precio_acordado', 12, 2)->nullable();
            $table->text('observaciones_servicio')->nullable();
            
            $table->boolean('es_transformada_de_cotizacion')->default(false);
            $table->decimal('monto_descuento_aplicado', 12, 2)->default(0.00);
            $table->date('fecha_para_invitar_nuevamente')->nullable();

            $table->timestamps();

            $table->index('estado_orden');
            $table->index('fecha_servicio_programada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ordenes');
    }
};