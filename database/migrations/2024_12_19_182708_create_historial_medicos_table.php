<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
     /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('historial_medico', function (Blueprint $table) {
            $table->id('id_historial');
            $table->foreignId('id_usuario')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('id_especialista')->constrained('especialistas')->onDelete('cascade');
            $table->foreignId('id_cita')->nullable()->constrained('citas')->onDelete('set null');
            $table->text('diagnostico');
            $table->text('tratamiento');
            $table->dateTime('fecha');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_medico');
    }
};
