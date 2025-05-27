<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Electrodomestico extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
    ];

    /**
     * Un electrodoméstico puede estar en muchos detalles de órdenes.
     */
    public function ordenElectrodomesticos(): HasMany
    {
        return $this->hasMany(OrdenElectrodomestico::class, 'id_electrodomestico');
    }
}