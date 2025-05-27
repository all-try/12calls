<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiquidacionOrden extends Model
{
    use HasFactory;

    protected $table = 'liquidaciones_ordenes';

    protected $fillable = [
        'id_orden',
        'id_tecnico_liquida',
        'fecha_liquidacion',
        'monto_cobrado_efectivo',
        'monto_cobrado_transferencia',
        'referencia_transferencia',
        'observaciones_cobro',
        'monto_total_repuestos',
        'monto_otros_gastos',
        'descripcion_otros_gastos',
        'saldo_neto_liquidacion',
        'estado_liquidacion',
        'observaciones_internas_liquidacion',
        'id_usuario_revisa',
        'fecha_revision',
    ];

    protected $casts = [
        'fecha_liquidacion' => 'datetime',
        'monto_cobrado_efectivo' => 'decimal:2',
        'monto_cobrado_transferencia' => 'decimal:2',
        'monto_total_repuestos' => 'decimal:2',
        'monto_otros_gastos' => 'decimal:2',
        'saldo_neto_liquidacion' => 'decimal:2',
        'fecha_revision' => 'datetime',
    ];

    /**
     * La liquidación pertenece a una orden.
     */
    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class, 'id_orden');
    }

    /**
     * La liquidación fue realizada por un técnico.
     */
    public function tecnicoLiquidador(): BelongsTo
    {
        return $this->belongsTo(Tecnico::class, 'id_tecnico_liquida');
    }

    /**
     * La liquidación puede ser revisada por un usuario (admin).
     */
    public function usuarioRevisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario_revisa');
    }

    /**
     * Una liquidación tiene muchos repuestos gastados.
     */
    public function repuestosGastados(): HasMany
    {
        return $this->hasMany(LiquidacionRepuestoGastado::class, 'id_liquidacion_orden');
    }
}