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
        Schema::create('liquidaciones_ordenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_orden')->unique()->constrained('ordenes')->onDelete('restrict');
            $table->foreignId('id_tecnico_liquida')->constrained('tecnicos')->onDelete('restrict');
            $table->dateTime('fecha_liquidacion')->useCurrent(); // Usa la fecha actual por defecto
            $table->decimal('monto_cobrado_efectivo', 12, 2)->default(0.00);
            $table->decimal('monto_cobrado_transferencia', 12, 2)->default(0.00);
            $table->string('referencia_transferencia', 100)->nullable();
            $table->text('observaciones_cobro')->nullable();
            $table->decimal('monto_total_repuestos', 12, 2)->default(0.00);
            $table->decimal('monto_otros_gastos', 12, 2)->default(0.00);
            $table->text('descripcion_otros_gastos')->nullable();
            $table->decimal('saldo_neto_liquidacion', 12, 2); // Se calcula en la aplicación
            $table->enum('estado_liquidacion', [
                'PENDIENTE_ENTREGA','ENTREGADA_PENDIENTE_REVISION',
                'APROBADA', 'RECHAZADA_AJUSTES', 'CERRADA'
            ])->default('PENDIENTE_ENTREGA');
            $table->text('observaciones_internas_liquidacion')->nullable();
            $table->foreignId('id_usuario_revisa')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('fecha_revision')->nullable();
            $table->timestamps();

            $table->index('estado_liquidacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidaciones_ordenes');
    }
};