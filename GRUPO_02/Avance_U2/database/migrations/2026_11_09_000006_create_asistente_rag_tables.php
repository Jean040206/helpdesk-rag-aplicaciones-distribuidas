<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistente_rag_evaluaciones', function (Blueprint $table) {
            $table->increments('id_evaluacion_rag');
            $table->unsignedInteger('id_ticket');
            $table->unsignedInteger('id_articulo_recuperado')->nullable();
            $table->unsignedInteger('id_tecnico_evaluador')->nullable();
            $table->enum('metodo_ia', ['Recuperacion_KB', 'LLM_Sin_Contexto', 'Asistente_RAG'])
                ->default('Asistente_RAG');
            $table->string('proveedor', 40)->nullable();
            $table->string('modelo_utilizado', 80)->nullable();
            $table->string('categoria_sugerida', 100)->nullable();
            $table->string('prioridad_sugerida', 40)->nullable();
            $table->string('servicio_sugerido', 100)->nullable();
            $table->text('posible_causa')->nullable();
            $table->text('solucion_recomendada')->nullable();
            $table->enum('decision_tecnico', ['Pendiente', 'Aceptada', 'Modificada', 'Descartada'])
                ->default('Pendiente');
            $table->text('solucion_final_tecnico')->nullable();
            $table->integer('tiempo_procesamiento_ms')->default(0);
            $table->boolean('es_dato_sintetico')->default(false);
            $table->longText('prompt_enviado')->nullable();
            $table->longText('respuesta_cruda')->nullable();
            $table->json('respuesta_estructurada')->nullable();
            $table->string('estado', 20)->default('pendiente');
            $table->text('error_mensaje')->nullable();
            $table->integer('tokens_entrada')->nullable();
            $table->integer('tokens_salida')->nullable();
            $table->json('parametros')->nullable();
            $table->decimal('confianza', 5, 2)->nullable();
            $table->timestamp('fecha_evento')->useCurrent();
            $table->timestamp('fecha_decision')->nullable();
            $table->text('comentario_tecnico')->nullable();
            $table->engine = 'InnoDB';

            $table->foreign('id_ticket', 'fk_rag_ticket')
                ->references('id_ticket')->on('tickets')
                ->cascadeOnDelete();
            $table->foreign('id_articulo_recuperado', 'fk_rag_articulo')
                ->references('id_articulo')->on('base_conocimiento')
                ->nullOnDelete();
            $table->foreign('id_tecnico_evaluador', 'fk_rag_tecnico')
                ->references('id_usuario')->on('usuarios')
                ->nullOnDelete();

            $table->index(['id_ticket', 'estado'], 'idx_rag_ticket_estado');
        });

        Schema::create('asistente_rag_fuentes', function (Blueprint $table) {
            $table->increments('id_fuente');
            $table->unsignedInteger('id_evaluacion_rag');
            $table->unsignedInteger('id_articulo');
            $table->integer('posicion');
            $table->decimal('score', 8, 4)->nullable();
            $table->text('fragmento')->nullable();
            $table->timestamp('fecha_recuperacion')->useCurrent();
            $table->engine = 'InnoDB';

            $table->foreign('id_evaluacion_rag', 'fk_rag_fuente_evaluacion')
                ->references('id_evaluacion_rag')->on('asistente_rag_evaluaciones')
                ->cascadeOnDelete();
            $table->foreign('id_articulo', 'fk_rag_fuente_articulo')
                ->references('id_articulo')->on('base_conocimiento')
                ->cascadeOnDelete();

            $table->unique(['id_evaluacion_rag', 'id_articulo'], 'uq_rag_fuente_articulo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistente_rag_fuentes');
        Schema::dropIfExists('asistente_rag_evaluaciones');
    }
};
