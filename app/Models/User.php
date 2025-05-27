<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail; // Descomenta si usas verificación de email
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser; // <--- AÑADIDO: Para Filament
use Filament\Panel; // <--- AÑADIDO: Para Filament
use Illuminate\Database\Eloquent\Relations\HasMany; // <--- AÑADIDO: Para relaciones

class User extends Authenticatable implements FilamentUser // <--- MODIFICADO: implementa FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',      // <--- AÑADIDO
        'activo',   // <--- AÑADIDO
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean', // <--- AÑADIDO: para que 'activo' se maneje como booleano
        ];
    }

    // --- MÉTODOS HELPER PARA ROLES ---
    public function isSuperAdmin(): bool
    {
        return $this->rol === 'SUPER_ADMIN';
    }

    public function isAdmin(): bool
    {
        // Un SUPER_ADMIN también es ADMIN en términos de permisos generales de admin
        return $this->rol === 'ADMIN' || $this->rol === 'SUPER_ADMIN';
    }

    public function isAsesor(): bool
    {
        return $this->rol === 'ASESOR';
    }

    // --- IMPLEMENTACIÓN PARA FilamentUser ---
    /**
     * Determina si el usuario puede acceder al panel de Filament.
     * ¡IMPORTANTE! Ajusta esta lógica para producción.
     * Por defecto, Filament permite acceso local a todos los usuarios.
     * En producción, debes definir quién puede acceder.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Ejemplo: Solo permite acceso a usuarios activos con roles SUPER_ADMIN, ADMIN o ASESOR
        // y que tengan el email verificado (si usas verificación de email).
        // En desarrollo (APP_ENV=local), esto podría ser menos restrictivo.
        if (app()->environment('local')) {
            return $this->activo && in_array($this->rol, ['SUPER_ADMIN', 'ADMIN', 'ASESOR']);
        }
        
        // Lógica para producción:
        // return $this->activo &&
        //        in_array($this->rol, ['SUPER_ADMIN', 'ADMIN', 'ASESOR']) &&
        //        $this->hasVerifiedEmail(); // Descomenta si usas MustVerifyEmail
        
        // Una lógica más simple para empezar en producción podría ser:
        return $this->activo && ($this->isSuperAdmin() || $this->isAdmin() || $this->isAsesor());
    }

    // --- RELACIONES ELOQUENT ---

    /**
     * Un usuario (asesor) puede tener muchos contactos asignados.
     */
    public function contactosAsignados(): HasMany
    {
       return $this->hasMany(Contacto::class, 'id_asesor');
    }

    /**
     * Un usuario (asesor) puede haber creado muchas órdenes.
     */
    public function ordenesCreadas(): HasMany
    {
       return $this->hasMany(Orden::class, 'id_asesor_creo');
    }

    /**
     * Un usuario (admin/revisor) puede haber revisado muchas liquidaciones.
     */
    public function liquidacionesRevisadas(): HasMany
    {
       return $this->hasMany(LiquidacionOrden::class, 'id_usuario_revisa');
    }
}