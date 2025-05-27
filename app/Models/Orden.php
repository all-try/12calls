<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Orden extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_orden',
        'id_contacto',
        'id_tecnico',
        'id_asesor_creo',
        'direccion_servicio',
        'id_barrio_servicio',
        'tipo_servicio',
        'estado_orden',
        // 'fecha_orden', // Cubierta por created_at
        'fecha_servicio_programada',
        'hora_servicio_programada',
        'fecha_servicio_realizada',
        'precio_acordado',
        'observaciones_servicio',
    ];

    protected $casts = [
        'fecha_servicio_programada' => 'date',
        'hora_servicio_programada' => 'datetime:H:i', // Ajustar formato si es necesario
        'fecha_servicio_realizada' => 'datetime',
        'precio_acordado' => 'decimal:2',
    ];

    /**
     * La orden pertenece a un contacto (cliente).
     */
    public function contacto(): BelongsTo
    {
        return $this->belongsTo(Contacto::class, 'id_contacto');
    }

    /**
     * La orden puede estar asignada a un técnico.
     */
    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(Tecnico::class, 'id_tecnico');
    }

    /**
     * La orden fue creada por un asesor (User).
     */
    public function asesorCreador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_asesor_creo');
    }

    /**
     * La orden se realiza en un barrio.
     */
    public function barrioServicio(): BelongsTo
    {
        return $this->belongsTo(Barrio::class, 'id_barrio_servicio');
    }

    /**
     * Una orden tiene muchos ítems de electrodomésticos.
     */
    public function ordenElectrodomesticos(): HasMany
    {
        return $this->hasMany(OrdenElectrodomestico::class, 'id_orden');
    }

    /**
     * Una orden tiene una liquidación asociada.
     */
    public function liquidacionOrden(): HasOne
    {
        return $this->hasOne(LiquidacionOrden::class, 'id_orden');
    }
}