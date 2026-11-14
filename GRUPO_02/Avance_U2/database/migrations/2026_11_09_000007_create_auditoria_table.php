<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->increments('id_auditoria');
            $table->unsignedInteger('id_usuario')->nullable();
            $table->string('modulo', 60);
            $table->string('accion', 80);
            $table->text('descripcion_evento');
            $table->string('ip_origen', 45)->default('127.0.0.1');
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('fecha_evento')->useCurrent();
            $table->engine = 'InnoDB';

            $table->foreign('id_usuario', 'fk_auditoria_usuario')
                ->references('id_usuario')->on('usuarios')
                ->nullOnDelete();

            $table->index(['modulo', 'accion'], 'idx_auditoria_modulo_accion');
            $table->index('fecha_evento', 'idx_auditoria_fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};
