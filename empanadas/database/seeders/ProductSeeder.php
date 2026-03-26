<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $productos = [
            [
                'nombre'        => 'Empanada de Pollo',
                'descripcion'   => 'Empanada tradicional rellena de pollo desmenuzado con papa y ají',
                'precio'        => 2500,
                'relleno'       => 'Pollo',
                'tamano'        => 'Personal',
                'tipo_producto' => 'empanada',
                'activo'        => true,
                'stock'         => 50,
            ],
            [
                'nombre'        => 'Empanada de Carne',
                'descripcion'   => 'Rellena de carne molida con papa y especias',
                'precio'        => 2500,
                'relleno'       => 'Carne',
                'tamano'        => 'Personal',
                'tipo_producto' => 'empanada',
                'activo'        => true,
                'stock'         => 50,
            ],
            [
                'nombre'        => 'Empanada Mixta',
                'descripcion'   => 'Combinación de pollo y carne con papa',
                'precio'        => 2800,
                'relleno'       => 'Mixto',
                'tamano'        => 'Personal',
                'tipo_producto' => 'empanada',
                'activo'        => true,
                'stock'         => 40,
            ],
            [
                'nombre'        => 'Empanada Hawaiana',
                'descripcion'   => 'Pollo, piña y queso fundido',
                'precio'        => 3000,
                'relleno'       => 'Hawaiano',
                'tamano'        => 'Personal',
                'tipo_producto' => 'empanada',
                'activo'        => true,
                'stock'         => 30,
            ],
            [
                'nombre'        => 'Empanada de Queso',
                'descripcion'   => 'Queso blanco derretido, ideal para veganos del queso',
                'precio'        => 2200,
                'relleno'       => 'Queso',
                'tamano'        => 'Personal',
                'tipo_producto' => 'empanada',
                'activo'        => true,
                'stock'         => 35,
            ],
            [
                'nombre'        => 'Empanada Grande de Pollo',
                'descripcion'   => 'Versión grande, ideal para compartir',
                'precio'        => 4500,
                'relleno'       => 'Pollo',
                'tamano'        => 'Grande',
                'tipo_producto' => 'empanada',
                'activo'        => true,
                'stock'         => 20,
            ],

            [
                'nombre'        => 'Papa Rellena de Pollo',
                'descripcion'   => 'Papa criolla rellena de pollo guisado con hogao',
                'precio'        => 3500,
                'relleno'       => 'Pollo',
                'tamano'        => 'Personal',
                'tipo_producto' => 'papa_rellena',
                'activo'        => true,
                'stock'         => 40,
            ],
            [
                'nombre'        => 'Papa Rellena de Carne',
                'descripcion'   => 'Papa criolla con carne molida y especias al gusto',
                'precio'        => 3500,
                'relleno'       => 'Carne',
                'tamano'        => 'Personal',
                'tipo_producto' => 'papa_rellena',
                'activo'        => true,
                'stock'         => 40,
            ],
            [
                'nombre'        => 'Papa Rellena Mixta',
                'descripcion'   => 'Pollo y carne juntos, doble sabor',
                'precio'        => 4000,
                'relleno'       => 'Mixto',
                'tamano'        => 'Personal',
                'tipo_producto' => 'papa_rellena',
                'activo'        => true,
                'stock'         => 30,
            ],
            [
                'nombre'        => 'Papa Rellena de Chorizo',
                'descripcion'   => 'Con chorizo casero y queso fundido',
                'precio'        => 4200,
                'relleno'       => 'Chorizo',
                'tamano'        => 'Personal',
                'tipo_producto' => 'papa_rellena',
                'activo'        => true,
                'stock'         => 25,
            ],
        ];

        foreach ($productos as $datos) {
            Product::firstOrCreate(
                ['nombre' => $datos['nombre'], 'tamano' => $datos['tamano']],
                $datos
            );
        }
    }
}