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
        Schema::create('orden_electrodomesticos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_orden')->constrained('ordenes')->onDelete('cascade');
            $table->foreignId('id_electrodomestico')->constrained('electrodomesticos')->onDelete('restrict');
            $table->string('marca_especifica', 100)->nullable();
            $table->string('modelo_especifico', 100)->nullable();
            $table->string('serie_electrodomestico', 100)->nullable();
            $table->text('descripcion_falla_especifica')->nullable();
            $table->text('diagnostico_tecnico_item')->nullable();
            $table->text('trabajo_realizado_item')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_electrodomesticos');
    }
};