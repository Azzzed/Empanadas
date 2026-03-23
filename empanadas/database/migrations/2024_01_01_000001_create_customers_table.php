    <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento', 20)->comment('CC, NIT, CE, PASAPORTE');
            $table->string('numero_documento', 20)->unique();
            $table->string('nombre', 150);
            $table->string('direccion', 255)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('numero_documento');
            $table->index('nombre');
        });

        // Insertar el cliente de mostrador por defecto (ID fijo = 1)
        DB::table('customers')->insert([
            'id'               => 1,
            'tipo_documento'   => 'CC',
            'numero_documento' => '0000000000',
            'nombre'           => 'Cliente de Mostrador',
            'activo'           => true,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
