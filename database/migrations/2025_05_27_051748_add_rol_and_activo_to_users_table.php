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
        Schema::table('users', function (Blueprint $table) {
            // Asumimos que 'password' es una columna existente.
            // Ajusta los roles según tus necesidades.
            $table->enum('rol', ['SUPER_ADMIN', 'ADMIN', 'ASESOR'])->default('ASESOR')->after('password');
            $table->boolean('activo')->default(true)->after('rol');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['rol', 'activo']);
        });
    }
};