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
        Schema::create('garantias_orden', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_orden_original')->constrained('ordenes')->onDelete('cascade');
            $table->foreignId('id_orden_de_garantia')->nullable()->unique()->constrained('ordenes')->onDelete('set null');
            
            $table->dateTime('fecha_solicitud')->useCurrent();
            $table->text('descripcion_falla_garantia');
            $table->foreignId('id_asesor_registro')->constrained('users')->onDelete('restrict');

            $table->enum('estado_garantia', [
                'SOLICITADA',
                'EN_REVISION_ADMIN',
                'APROBADA_PENDIENTE_ORDEN',
                'RECHAZADA',
                'ORDEN_DE_GARANTIA_CREADA',
                'SERVICIO_GARANTIA_COMPLETADO' // Este estado indica que la orden de garantía se completó
            ])->default('SOLICITADA');
            
            $table->text('observaciones_admin')->nullable();
            $table->dateTime('fecha_decision_admin')->nullable();
            $table->foreignId('id_admin_decision')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            $table->index('estado_garantia');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garantias_orden');
    }
};