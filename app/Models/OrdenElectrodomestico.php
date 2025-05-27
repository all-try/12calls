<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenElectrodomestico extends Model
{
    use HasFactory;

    protected $table = 'orden_electrodomesticos'; // Especificar nombre de tabla si es diferente a la convención

    protected $fillable = [
        'id_orden',
        'id_electrodomestico',
        'marca_especifica',
        'modelo_especifico',
        'serie_electrodomestico',
        'descripcion_falla_especifica',
        'diagnostico_tecnico_item',
        'trabajo_realizado_item',
    ];

    /**
     * Este ítem pertenece to una orden.
     */
    public function orden(): BelongsTo
    {
        return $this->belongsTo(Orden::class, 'id_orden');
    }

    /**
     * Este ítem se refiere a un tipo de electrodoméstico.
     */
    public function electrodomestico(): BelongsTo
    {
        return $this->belongsTo(Electrodomestico::class, 'id_electrodomestico');
    }
}