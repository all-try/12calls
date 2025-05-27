<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiquidacionRepuestoGastado extends Model
{
    use HasFactory;

    protected $table = 'liquidacion_repuestos_gastados';

    protected $fillable = [
        'id_liquidacion_orden',
        'nombre_repuesto',
        'cantidad',
        'precio_unitario_compra',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario_compra' => 'decimal:2',
    ];

    /**
     * Este repuesto gastado pertenece a una liquidación de orden.
     */
    public function liquidacionOrden(): BelongsTo
    {
        return $this->belongsTo(LiquidacionOrden::class, 'id_liquidacion_orden');
    }

    // Atributo calculado para el precio total (opcional)
    public function getPrecioTotalCompraAttribute(): float
    {
        return $this->cantidad * $this->precio_unitario_compra;
    }
}