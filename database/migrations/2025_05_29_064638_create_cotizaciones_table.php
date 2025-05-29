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
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_orden_origen')->constrained('ordenes')->onDelete('cascade');
            $table->foreignId('id_asesor_encargado')->nullable()->constrained('users')->onDelete('set null');
            
            $table->text('descripcion_trabajo_propuesto');
            $table->decimal('monto_total_propuesto', 12, 2);
            $table->decimal('monto_revision_original', 12, 2)->default(0.00);
            
            $table->enum('estado_cotizacion', [
                'PENDIENTE_LLAMADA_ASESOR',
                'REINTENTAR_CONTACTO',
                'ACEPTADA', // La orden original se modifica
                'RECHAZADA_SEGUIMIENTO',
                'RECHAZADA_FINAL'
            ])->default('PENDIENTE_LLAMADA_ASESOR');
            
            $table->dateTime('fecha_decision_cliente')->nullable();
            $table->date('fecha_proximo_seguimiento')->nullable();
            $table->text('observaciones_seguimiento')->nullable();
            
            $table->timestamps();

            $table->index('estado_cotizacion');
            $table->index('fecha_proximo_seguimiento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotizaciones');
    }
};