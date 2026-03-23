<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -----------------------------------------------------------
        // Tabla: sales (cabecera de la venta)
        // -----------------------------------------------------------
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            // FK: customer — RESTRICT impide borrar un cliente con ventas
            $table->foreignId('customer_id')
                  ->default(1)
                  ->constrained('customers')
                  ->restrictOnDelete();

            $table->string('numero_factura', 30)->unique()->nullable()
                  ->comment('Número de factura generado automáticamente');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0)
                  ->comment('Valor absoluto del descuento');
            $table->decimal('descuento_porcentaje', 5, 2)->default(0)
                  ->comment('Porcentaje de descuento aplicado');
            $table->decimal('total', 12, 2)->default(0);

            // Métodos de pago — soporte múltiple como JSON
            $table->jsonb('metodos_pago')->nullable()
                  ->comment('[{"metodo":"efectivo","monto":10000},{"metodo":"transferencia","monto":5000}]');

            $table->string('estado', 20)->default('completada')
                  ->comment('completada | anulada | pendiente');

            $table->string('notas', 500)->nullable();

            // Campos de auditoría
            $table->unsignedBigInteger('cajero_id')->nullable()
                  ->comment('ID del usuario que procesó la venta');

            $table->timestamps();
            $table->softDeletes();

            $table->index('customer_id');
            $table->index('estado');
            $table->index('created_at');
        });

        // -----------------------------------------------------------
        // Tabla: sale_items (detalle/líneas de la venta)
        // -----------------------------------------------------------
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();

            // FK: sale — CASCADE borra ítems si se elimina la venta (anulación lógica)
            $table->foreignId('sale_id')
                  ->constrained('sales')
                  ->cascadeOnDelete();

            // FK: product — RESTRICT impide borrar un producto con ventas
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->restrictOnDelete();

            $table->integer('cantidad')->default(1);
            $table->decimal('precio_unitario', 10, 2)
                  ->comment('Precio en el momento de la venta (snapshot)');
            $table->decimal('descuento_item', 10, 2)->default(0);
            $table->decimal('subtotal', 12, 2)
                  ->comment('(precio_unitario * cantidad) - descuento_item');

            $table->string('notas_item', 255)->nullable()
                  ->comment('Personalización: sin sal, extra picante, etc.');

            $table->timestamps();

            $table->index('sale_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
