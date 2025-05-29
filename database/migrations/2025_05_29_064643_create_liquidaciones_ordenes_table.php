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
            $table->foreignId('id_usuario_registro_liquidacion')->constrained('users')->onDelete('restrict');
            
            $table->dateTime('fecha_liquidacion_registrada')->useCurrent();
            
            $table->decimal('monto_cobrado_efectivo', 12, 2)->default(0.00);
            $table->decimal('monto_cobrado_transferencia', 12, 2)->default(0.00);
            $table->string('referencia_transferencia', 100)->nullable();
            $table->text('observaciones_cobro')->nullable();
            
            $table->decimal('monto_total_repuestos', 12, 2)->default(0.00);
            $table->decimal('monto_otros_gastos', 12, 2)->default(0.00);
            $table->text('descripcion_otros_gastos')->nullable();
            
            $table->decimal('monto_total_ingresos_cliente', 12, 2);
            $table->decimal('monto_total_gastos_tecnico', 12, 2);
            $table->decimal('saldo_neto_liquidacion', 12, 2);
            $table->decimal('monto_para_tecnico', 12, 2);
            $table->decimal('monto_para_empresa', 12, 2);
            
            $table->text('observaciones_generales_liquidacion')->nullable();
            $table->timestamps();

            $table->index('id_tecnico_liquida');
            // No hay estado_liquidacion en esta tabla según la última decisión
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