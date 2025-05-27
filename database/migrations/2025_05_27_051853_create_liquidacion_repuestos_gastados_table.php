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
        Schema::create('liquidacion_repuestos_gastados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_liquidacion_orden')->constrained('liquidaciones_ordenes')->onDelete('cascade');
            $table->string('nombre_repuesto', 255);
            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario_compra', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('liquidacion_repuestos_gastados');
    }
};