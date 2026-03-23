<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10, 2);
            $table->string('relleno', 100)->comment('Ej: Carne, Pollo, Queso, Mixto');
            $table->string('tamano', 50)->comment('Ej: Personal, Mediana, Grande, Familiar');
            $table->string('tipo_producto', 30)->default('empanada')
                  ->comment('empanada | papa_rellena');
            $table->string('imagen_path', 255)->nullable()->comment('Ruta para imagen futura');
            $table->boolean('activo')->default(true);
            $table->integer('stock')->default(0)->comment('Stock del día');
            $table->timestamps();
            $table->softDeletes();

            $table->index('tipo_producto');
            $table->index('activo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
