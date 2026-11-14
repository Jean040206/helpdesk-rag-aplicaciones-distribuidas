<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id_rol');
            $table->string('nombre', 50)->unique();
            $table->string('descripcion', 200);
            $table->engine = 'InnoDB';
        });

        Schema::create('servicios', function (Blueprint $table) {
            $table->increments('id_servicio');
            $table->string('codigo_servicio', 20)->unique();
            $table->string('nombre', 100)->unique();
            $table->string('descripcion', 255);
            $table->boolean('activo')->default(true);
            $table->engine = 'InnoDB';
        });

        Schema::create('categorias', function (Blueprint $table) {
            $table->increments('id_categoria');
            $table->unsignedInteger('id_servicio_default')->nullable();
            $table->string('nombre', 100)->unique();
            $table->string('descripcion', 200);
            $table->engine = 'InnoDB';

            $table->foreign('id_servicio_default', 'fk_categoria_servicio')
                ->references('id_servicio')->on('servicios')
                ->cascadeOnUpdate()->nullOnDelete();
        });

        Schema::create('prioridades', function (Blueprint $table) {
            $table->increments('id_prioridad');
            $table->string('nombre', 40)->unique();
            $table->integer('nivel_orden');
            $table->integer('sla_horas')->default(24);
            $table->string('color_badge', 30)->default('secondary');
            $table->engine = 'InnoDB';
        });

        Schema::create('estados', function (Blueprint $table) {
            $table->increments('id_estado');
            $table->string('nombre', 40)->unique();
            $table->string('descripcion', 150);
            $table->boolean('es_cerrado')->default(false);
            $table->string('color_badge', 30)->default('primary');
            $table->engine = 'InnoDB';
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados');
        Schema::dropIfExists('prioridades');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('servicios');
        Schema::dropIfExists('roles');
    }
};
