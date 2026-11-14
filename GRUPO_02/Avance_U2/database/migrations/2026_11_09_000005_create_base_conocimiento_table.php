<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('base_conocimiento', function (Blueprint $table) {
            $table->increments('id_articulo');
            $table->string('codigo_articulo', 20)->unique();
            $table->unsignedInteger('id_servicio');
            $table->unsignedInteger('id_categoria');
            $table->unsignedInteger('id_autor');
            $table->enum('tipo_documento', [
                'Manual',
                'Procedimiento',
                'FAQ',
                'Solucion_Anterior',
                'Doc_Tecnica',
            ]);
            $table->string('titulo', 180);
            $table->text('sintomas_problema');
            $table->text('pasos_solucion');
            $table->string('palabras_clave', 200);
            $table->boolean('activo')->default(true);
            $table->timestamp('fecha_publicacion')->useCurrent();
            $table->engine = 'InnoDB';

            $table->foreign('id_servicio', 'fk_kb_servicio')
                ->references('id_servicio')->on('servicios')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_categoria', 'fk_kb_categoria')
                ->references('id_categoria')->on('categorias')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_autor', 'fk_kb_autor')
                ->references('id_usuario')->on('usuarios')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->fullText(
                ['titulo', 'sintomas_problema', 'pasos_solucion', 'palabras_clave'],
                'idx_kb_busqueda_rag'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('base_conocimiento');
    }
};
