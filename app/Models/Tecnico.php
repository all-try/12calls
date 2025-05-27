<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tecnico extends Model
{
    use HasFactory;

    protected $fillable = [
        'cedula',
        'nombre',
        'apellido',
        'telefono',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Un técnico puede tener muchas órdenes asignadas.
     */
    public function ordenesAsignadas(): HasMany
    {
        return $this->hasMany(Orden::class, 'id_tecnico');
    }

    /**
     * Un técnico puede realizar muchas liquidaciones.
     */
    public function liquidacionesRealizadas(): HasMany
    {
        return $this->hasMany(LiquidacionOrden::class, 'id_tecnico_liquida');
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }
}