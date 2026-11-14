<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id_usuario');
            $table->unsignedInteger('id_rol');
            $table->string('codigo_institucional', 20)->unique();
            $table->string('nombre_completo', 120);
            $table->string('email', 120)->unique();
            $table->string('password_hash', 255);
            $table->string('dependencia_oficina', 100);
            $table->string('especialidad_ti', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamp('creado_en')->useCurrent();
            $table->rememberToken();
            $table->engine = 'InnoDB';

            $table->foreign('id_rol', 'fk_usuarios_rol')
                ->references('id_rol')->on('roles')
                ->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
