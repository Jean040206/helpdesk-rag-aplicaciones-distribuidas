<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id_ticket');
            $table->string('codigo_ticket', 25)->unique();
            $table->unsignedInteger('id_solicitante');
            $table->unsignedInteger('id_tecnico_asignado')->nullable();
            $table->unsignedInteger('id_supervisor_asignador')->nullable();
            $table->unsignedInteger('id_servicio');
            $table->unsignedInteger('id_categoria');
            $table->unsignedInteger('id_prioridad');
            $table->unsignedInteger('id_estado')->default(1);
            $table->string('asunto', 180);
            $table->text('descripcion');
            $table->text('diagnostico_tecnico')->nullable();
            $table->text('solucion_aplicada')->nullable();
            $table->timestamp('fecha_registro')->useCurrent();
            $table->dateTime('fecha_asignacion')->nullable();
            $table->dateTime('fecha_resolucion')->nullable();
            $table->engine = 'InnoDB';

            $table->foreign('id_solicitante', 'fk_ticket_solicitante')
                ->references('id_usuario')->on('usuarios')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_tecnico_asignado', 'fk_ticket_tecnico')
                ->references('id_usuario')->on('usuarios')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_supervisor_asignador', 'fk_ticket_supervisor')
                ->references('id_usuario')->on('usuarios')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_servicio', 'fk_ticket_servicio')
                ->references('id_servicio')->on('servicios')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_categoria', 'fk_ticket_categoria')
                ->references('id_categoria')->on('categorias')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_prioridad', 'fk_ticket_prioridad')
                ->references('id_prioridad')->on('prioridades')
                ->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('id_estado', 'fk_ticket_estado')
                ->references('id_estado')->on('estados')
                ->cascadeOnUpdate()->restrictOnDelete();

            $table->index(['id_estado', 'id_prioridad'], 'idx_tickets_estado_prioridad');
            $table->index(['id_solicitante', 'fecha_registro'], 'idx_tickets_solicitante_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
