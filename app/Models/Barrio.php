<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Barrio extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'zona',
    ];

    /**
     * Un barrio puede tener muchos contactos.
     */
    public function contactos(): HasMany
    {
        return $this->hasMany(Contacto::class, 'id_barrio');
    }

    /**
     * En un barrio se pueden realizar muchas órdenes de servicio.
     */
    public function ordenesDeServicio(): HasMany
    {
        return $this->hasMany(Orden::class, 'id_barrio_servicio');
    }
}