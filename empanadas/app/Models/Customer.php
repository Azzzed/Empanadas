<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Customer extends Model
{
    use SoftDeletes;

    // ---------------------------------------------------------------
    // Constante: ID reservado para el cliente de mostrador
    // ---------------------------------------------------------------
    public const MOSTRADOR_ID = 1;

    protected $fillable = [
        'tipo_documento',
        'numero_documento',
        'nombre',
        'direccion',
        'ciudad',
        'telefono',
        'email',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    // ---------------------------------------------------------------
    // Accessors
    // ---------------------------------------------------------------

    public function getEsMostradorAttribute(): bool
    {
        return $this->id === self::MOSTRADOR_ID;
    }

    public function getDocumentoCompletoAttribute(): string
    {
        return "{$this->tipo_documento}: {$this->numero_documento}";
    }

    // ---------------------------------------------------------------
    // Local Scopes — para facilitar consultas en informes
    // ---------------------------------------------------------------

    /** Clientes reales (excluye al de mostrador) */
    public function scopeReales(Builder $query): Builder
    {
        return $query->where('id', '!=', self::MOSTRADOR_ID);
    }

    /** Solo clientes activos */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    /** Búsqueda rápida POS por nombre o documento */
    public function scopeBuscar(Builder $query, string $termino): Builder
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'ilike', "%{$termino}%")
              ->orWhere('numero_documento', 'ilike', "%{$termino}%")
              ->orWhere('telefono', 'ilike', "%{$termino}%");
        });
    }

    /** Filtrar por ciudad */
    public function scopePorCiudad(Builder $query, string $ciudad): Builder
    {
        return $query->where('ciudad', 'ilike', "%{$ciudad}%");
    }

    // ---------------------------------------------------------------
    // Helpers de negocio
    // ---------------------------------------------------------------

    /** Verifica si el cliente tiene ventas registradas (para evitar borrado) */
    public function tieneSalesAsociadas(): bool
    {
        return $this->sales()->withTrashed()->exists();
    }
}
