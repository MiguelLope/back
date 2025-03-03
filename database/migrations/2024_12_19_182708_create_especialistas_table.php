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
        Schema::create('especialistas', function (Blueprint $table) {
            $table->id('id_especialista');
            $table->foreignId('id_usuario')->constrained('usuarios')->onDelete('cascade');
            $table->string('especialidad', 100);
            $table->foreignId('id_consultorio')->constrained('consultorios')->onDelete('set null')->nullable();
            $table->time('horario_inicio');
            $table->time('horario_fin');
            $table->string('dias_trabajo', 160);
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('especialistas');
    }
};
