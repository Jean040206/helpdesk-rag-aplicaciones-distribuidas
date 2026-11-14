<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_evidencias', function (Blueprint $table) {
            $table->increments('id_evidencia');
            $table->unsignedInteger('id_ticket');
            $table->string('nombre_original', 180);
            $table->string('ruta_archivo', 255);
            $table->string('extension', 20);
            $table->integer('tamano_kb')->default(0);
            $table->timestamp('fecha_subida')->useCurrent();
            $table->engine = 'InnoDB';

            $table->foreign('id_ticket', 'fk_evidencia_ticket')
                ->references('id_ticket')->on('tickets')
                ->cascadeOnDelete();
        });

        Schema::create('ticket_seguimiento', function (Blueprint $table) {
            $table->increments('id_seguimiento');
            $table->unsignedInteger('id_ticket');
            $table->unsignedInteger('id_usuario');
            $table->text('comentario');
            $table->enum('tipo_registro', ['Comentario', 'Diagnostico', 'Solucion'])
                ->default('Comentario');
            $table->timestamp('fecha_registro')->useCurrent();
            $table->engine = 'InnoDB';

            $table->foreign('id_ticket', 'fk_seguimiento_ticket')
                ->references('id_ticket')->on('tickets')
                ->cascadeOnDelete();
            $table->foreign('id_usuario', 'fk_seguimiento_usuario')
                ->references('id_usuario')->on('usuarios')
                ->restrictOnDelete();
        });

        Schema::create('ticket_historial', function (Blueprint $table) {
            $table->increments('id_historial');
            $table->unsignedInteger('id_ticket');
            $table->unsignedInteger('id_estado_anterior')->nullable();
            $table->unsignedInteger('id_estado_nuevo');
            $table->unsignedInteger('id_usuario_responsable');
            $table->string('observacion', 255);
            $table->timestamp('fecha_cambio')->useCurrent();
            $table->engine = 'InnoDB';

            $table->foreign('id_ticket', 'fk_hist_ticket')
                ->references('id_ticket')->on('tickets')
                ->cascadeOnDelete();
            $table->foreign('id_estado_anterior', 'fk_hist_est_ant')
                ->references('id_estado')->on('estados')
                ->restrictOnDelete();
            $table->foreign('id_estado_nuevo', 'fk_hist_est_nue')
                ->references('id_estado')->on('estados')
                ->restrictOnDelete();
            $table->foreign('id_usuario_responsable', 'fk_hist_usuario')
                ->references('id_usuario')->on('usuarios')
                ->restrictOnDelete();

            $table->index(['id_ticket', 'fecha_cambio'], 'idx_hist_ticket_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_historial');
        Schema::dropIfExists('ticket_seguimiento');
        Schema::dropIfExists('ticket_evidencias');
    }
};
