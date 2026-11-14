<?php

namespace Database\Seeders;

use App\Models\Auditoria;
use App\Models\Categoria;
use App\Models\Estado;
use App\Models\Prioridad;
use App\Models\Servicio;
use App\Models\Ticket;
use App\Models\TicketHistorial;
use App\Models\TicketSeguimiento;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class TicketDemoSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = Usuario::pluck('id_usuario', 'email');
        $servicios = Servicio::pluck('id_servicio', 'codigo_servicio');
        $categorias = Categoria::pluck('id_categoria', 'nombre');
        $prioridades = Prioridad::pluck('id_prioridad', 'nombre');
        $estados = Estado::pluck('id_estado', 'nombre');

        $tickets = [
            [
                'codigo_ticket' => 'TKT-2026-0001',
                'id_solicitante' => $usuarios['docente.demo@demo.test'],
                'id_tecnico_asignado' => $usuarios['tecnico1.demo@demo.test'],
                'id_supervisor_asignador' => $usuarios['supervisor.demo@demo.test'],
                'id_servicio' => $servicios['SRV-SIS'],
                'id_categoria' => $categorias['Acceso y Credenciales Incorrectas'],
                'id_prioridad' => $prioridades['Alta'],
                'id_estado' => $estados['Asignado'],
                'asunto' => 'No puedo acceder al sistema académico',
                'descripcion' => 'No puedo acceder al sistema académico. Al ingresar mi usuario y contraseña aparece un mensaje indicando que las credenciales son incorrectas.',
                'diagnostico_tecnico' => 'Se verificó en el log de autenticación que la cuenta docente superó el límite de intentos tras el cambio de semestre.',
                'fecha_asignacion' => now(),
            ],
            [
                'codigo_ticket' => 'TKT-2026-0002',
                'id_solicitante' => $usuarios['registro.demo@demo.test'],
                'id_tecnico_asignado' => $usuarios['tecnico1.demo@demo.test'],
                'id_supervisor_asignador' => $usuarios['supervisor.demo@demo.test'],
                'id_servicio' => $servicios['SRV-COR'],
                'id_categoria' => $categorias['Sincronización y Bloqueo de Correo'],
                'id_prioridad' => $prioridades['Alta'],
                'id_estado' => $estados['En Proceso'],
                'asunto' => 'El usuario no puede acceder al correo institucional',
                'descripcion' => 'El usuario no puede acceder al correo institucional desde esta mañana. Solicito restablecimiento o revisión de bloqueo de cuenta.',
                'diagnostico_tecnico' => 'Cuenta bloqueada preventivamente por sincronización con contraseña antigua en dispositivo móvil.',
                'fecha_asignacion' => now(),
            ],
            [
                'codigo_ticket' => 'TKT-2026-0003',
                'id_solicitante' => $usuarios['docente.demo@demo.test'],
                'id_tecnico_asignado' => null,
                'id_supervisor_asignador' => null,
                'id_servicio' => $servicios['SRV-IMP'],
                'id_categoria' => $categorias['Atasco o Desconfiguración de Impresora'],
                'id_prioridad' => $prioridades['Media'],
                'id_estado' => $estados['Abierto'],
                'asunto' => 'Impresora de red de escuela no imprime actas',
                'descripcion' => 'Al enviar documentos PDF a la impresora multifuncional de la oficina, los trabajos se quedan en cola y el equipo muestra error de conexión.',
                'diagnostico_tecnico' => null,
                'fecha_asignacion' => null,
            ],
        ];

        foreach ($tickets as $ticket) {
            Ticket::updateOrCreate(
                ['codigo_ticket' => $ticket['codigo_ticket']],
                $ticket
            );
        }

        $ticket1 = Ticket::where('codigo_ticket', 'TKT-2026-0001')->firstOrFail();
        $ticket2 = Ticket::where('codigo_ticket', 'TKT-2026-0002')->firstOrFail();
        $ticket3 = Ticket::where('codigo_ticket', 'TKT-2026-0003')->firstOrFail();

        $seguimientos = [
            [
                'id_ticket' => $ticket1->id_ticket,
                'id_usuario' => $usuarios['supervisor.demo@demo.test'],
                'comentario' => 'Ticket revisado por supervisión y asignado al Técnico Demo Uno por corresponder a Sistemas Institucionales.',
                'tipo_registro' => 'Comentario',
            ],
            [
                'id_ticket' => $ticket2->id_ticket,
                'id_usuario' => $usuarios['tecnico1.demo@demo.test'],
                'comentario' => 'Se consultó el procedimiento KB-2026-002 en la base de conocimiento para desbloqueo de cuenta institucional.',
                'tipo_registro' => 'Diagnostico',
            ],
        ];

        foreach ($seguimientos as $seguimiento) {
            TicketSeguimiento::firstOrCreate(
                [
                    'id_ticket' => $seguimiento['id_ticket'],
                    'id_usuario' => $seguimiento['id_usuario'],
                    'comentario' => $seguimiento['comentario'],
                ],
                ['tipo_registro' => $seguimiento['tipo_registro']]
            );
        }

        $historial = [
            [
                'id_ticket' => $ticket1->id_ticket,
                'id_estado_anterior' => null,
                'id_estado_nuevo' => $estados['Abierto'],
                'id_usuario_responsable' => $usuarios['docente.demo@demo.test'],
                'observacion' => 'Creación inicial del ticket por el usuario solicitante',
            ],
            [
                'id_ticket' => $ticket1->id_ticket,
                'id_estado_anterior' => $estados['Abierto'],
                'id_estado_nuevo' => $estados['Asignado'],
                'id_usuario_responsable' => $usuarios['supervisor.demo@demo.test'],
                'observacion' => 'Asignado al técnico Técnico Demo Uno por el supervisor',
            ],
            [
                'id_ticket' => $ticket2->id_ticket,
                'id_estado_anterior' => null,
                'id_estado_nuevo' => $estados['Abierto'],
                'id_usuario_responsable' => $usuarios['registro.demo@demo.test'],
                'observacion' => 'Creación inicial del ticket por el usuario solicitante',
            ],
            [
                'id_ticket' => $ticket2->id_ticket,
                'id_estado_anterior' => $estados['Abierto'],
                'id_estado_nuevo' => $estados['En Proceso'],
                'id_usuario_responsable' => $usuarios['tecnico1.demo@demo.test'],
                'observacion' => 'Ticket en proceso de diagnóstico con apoyo de Base de Conocimiento',
            ],
            [
                'id_ticket' => $ticket3->id_ticket,
                'id_estado_anterior' => null,
                'id_estado_nuevo' => $estados['Abierto'],
                'id_usuario_responsable' => $usuarios['docente.demo@demo.test'],
                'observacion' => 'Creación inicial del ticket por el usuario solicitante',
            ],
        ];

        foreach ($historial as $evento) {
            TicketHistorial::firstOrCreate(
                [
                    'id_ticket' => $evento['id_ticket'],
                    'id_estado_anterior' => $evento['id_estado_anterior'],
                    'id_estado_nuevo' => $evento['id_estado_nuevo'],
                    'id_usuario_responsable' => $evento['id_usuario_responsable'],
                ],
                ['observacion' => $evento['observacion']]
            );
        }

        $auditorias = [
            [
                'id_usuario' => $usuarios['docente.demo@demo.test'],
                'modulo' => 'Tickets',
                'accion' => 'REGISTRO_TICKET',
                'descripcion_evento' => 'Usuario registró el ticket TKT-2026-0001 (Sistemas Institucionales)',
            ],
            [
                'id_usuario' => $usuarios['supervisor.demo@demo.test'],
                'modulo' => 'Supervisión',
                'accion' => 'ASIGNAR_TECNICO',
                'descripcion_evento' => 'Supervisor asignó TKT-2026-0001 al técnico Técnico Demo Uno',
            ],
        ];

        foreach ($auditorias as $auditoria) {
            Auditoria::firstOrCreate(
                [
                    'modulo' => $auditoria['modulo'],
                    'accion' => $auditoria['accion'],
                    'descripcion_evento' => $auditoria['descripcion_evento'],
                ],
                ['id_usuario' => $auditoria['id_usuario']]
            );
        }
    }
}
