<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'relleno',
        'tamano',
        'tipo_producto',
        'imagen_path',
        'activo',
        'stock',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
        'stock'  => 'integer',
    ];

    // ---------------------------------------------------------------
    // Relaciones
    // ---------------------------------------------------------------

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    // ---------------------------------------------------------------
    // Accessors
    // ---------------------------------------------------------------

    public function getPrecioFormateadoAttribute(): string
    {
        return '$' . number_format($this->precio, 0, ',', '.');
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo_producto) {
            'papa_rellena' => 'Papa Rellena',
            default        => 'Empanada',
        };
    }

    public function getImagenUrlAttribute(): ?string
    {
        return $this->imagen_path
            ? asset('storage/' . $this->imagen_path)
            : null;
    }

    // ---------------------------------------------------------------
    // Local Scopes
    // ---------------------------------------------------------------

    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeEmpanadas(Builder $query): Builder
    {
        return $query->where('tipo_producto', 'empanada');
    }

    public function scopePapasRellenas(Builder $query): Builder
    {
        return $query->where('tipo_producto', 'papa_rellena');
    }

    public function scopePorRelleno(Builder $query, string $relleno): Builder
    {
        return $query->where('relleno', 'ilike', "%{$relleno}%");
    }

    /** Búsqueda rápida para el POS */
    public function scopeBuscar(Builder $query, string $termino): Builder
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'ilike', "%{$termino}%")
              ->orWhere('relleno', 'ilike', "%{$termino}%")
              ->orWhere('tipo_producto', 'ilike', "%{$termino}%");
        });
    }

    public function scopeDisponibles(Builder $query): Builder
    {
        return $query->where('activo', true)->where('stock', '>', 0);
    }

    // ---------------------------------------------------------------
    // Helpers de negocio
    // ---------------------------------------------------------------

    public function tieneVentasAsociadas(): bool
    {
        return $this->saleItems()->exists();
    }
}
