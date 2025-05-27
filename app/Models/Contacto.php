<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contacto extends Model
{
    use HasFactory;

    protected $fillable = [
        'cedula',
        'nombre_completo',
        'direccion',
        'id_barrio',
        'telefono_fijo',
        'celular',
        'telefono_opcional',
        'tipo_cliente',
        'id_asesor',
        'observaciones_cliente',
    ];

    /**
     * Un contacto pertenece a un barrio.
     */
    public function barrio(): BelongsTo
    {
        return $this->belongsTo(Barrio::class, 'id_barrio');
    }

    /**
     * Un contacto es gestionado por un asesor (User).
     */
    public function asesor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_asesor');
    }

    /**
     * Un contacto puede tener muchas órdenes.
     */
    public function ordenes(): HasMany
    {
        return $this->hasMany(Orden::class, 'id_contacto');
    }
}