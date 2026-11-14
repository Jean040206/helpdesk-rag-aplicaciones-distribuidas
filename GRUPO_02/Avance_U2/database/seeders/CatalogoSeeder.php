<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Estado;
use App\Models\Prioridad;
use App\Models\Rol;
use App\Models\Servicio;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'Solicitante', 'descripcion' => 'Usuario institucional que registra tickets de soporte y adjunta evidencias'],
            ['nombre' => 'Supervisor', 'descripcion' => 'Revisa las incidencias entrantes y las asigna a los técnicos correspondientes'],
            ['nombre' => 'Tecnico', 'descripcion' => 'Visualiza tickets asignados, consulta base de conocimiento/RAG y registra soluciones'],
            ['nombre' => 'Administrador', 'descripcion' => 'Gestiona usuarios, roles, catálogo de servicios, categorías y auditoría'],
        ];

        foreach ($roles as $rol) {
            Rol::updateOrCreate(['nombre' => $rol['nombre']], $rol);
        }

        $servicios = [
            [
                'codigo_servicio' => 'SRV-SIS',
                'nombre' => 'Sistemas Institucionales',
                'descripcion' => 'Sistema Académico, intranet docente, matrícula y gestión administrativa',
            ],
            [
                'codigo_servicio' => 'SRV-COR',
                'nombre' => 'Cuentas de Usuario y Correo',
                'descripcion' => 'Correo institucional @uns.edu.pe, Active Directory y credenciales SSO',
            ],
            [
                'codigo_servicio' => 'SRV-RED',
                'nombre' => 'Redes y Conectividad',
                'descripcion' => 'Red cableada, Wi-Fi de campus, switches, puntos de acceso y VPN',
            ],
            [
                'codigo_servicio' => 'SRV-EQU',
                'nombre' => 'Equipos Informáticos',
                'descripcion' => 'Computadoras de escritorio, estaciones de laboratorio y laptops asignadas',
            ],
            [
                'codigo_servicio' => 'SRV-SW',
                'nombre' => 'Software y Licencias',
                'descripcion' => 'Sistemas operativos, suites de ofimática, antivirus y software de ingeniería',
            ],
            [
                'codigo_servicio' => 'SRV-IMP',
                'nombre' => 'Impresoras y Periféricos',
                'descripcion' => 'Impresoras multifuncionales en red, escáneres y proyectores multimedia',
            ],
        ];

        foreach ($servicios as $servicio) {
            Servicio::updateOrCreate(
                ['codigo_servicio' => $servicio['codigo_servicio']],
                $servicio
            );
        }

        $categorias = [
            [
                'servicio' => 'SRV-SIS',
                'nombre' => 'Acceso y Credenciales Incorrectas',
                'descripcion' => 'Bloqueo de cuenta, olvido de clave o error de autenticación en sistemas',
            ],
            [
                'servicio' => 'SRV-COR',
                'nombre' => 'Sincronización y Bloqueo de Correo',
                'descripcion' => 'Fallas de ingreso al correo institucional, buzón lleno o rebote SMTP',
            ],
            [
                'servicio' => 'SRV-RED',
                'nombre' => 'Caída de Conectividad / Sin Internet',
                'descripcion' => 'Pérdida de conexión de red local, IP conflictiva o falla de Wi-Fi',
            ],
            [
                'servicio' => 'SRV-EQU',
                'nombre' => 'Falla de Hardware / No Enciende',
                'descripcion' => 'Averías físicas en fuente de poder, disco duro, memoria RAM o monitor',
            ],
            [
                'servicio' => 'SRV-SW',
                'nombre' => 'Instalación o Error de Software',
                'descripcion' => 'Cierre inesperado de programas, vencimiento de licencia o actualización',
            ],
            [
                'servicio' => 'SRV-IMP',
                'nombre' => 'Atasco o Desconfiguración de Impresora',
                'descripcion' => 'Cola de impresión bloqueada, error de driver en red o falta de tóner',
            ],
        ];

        foreach ($categorias as $categoria) {
            Categoria::updateOrCreate(
                ['nombre' => $categoria['nombre']],
                [
                    'id_servicio_default' => Servicio::where('codigo_servicio', $categoria['servicio'])->value('id_servicio'),
                    'descripcion' => $categoria['descripcion'],
                ]
            );
        }

        $prioridades = [
            ['nombre' => 'Baja', 'nivel_orden' => 1, 'sla_horas' => 48, 'color_badge' => 'info'],
            ['nombre' => 'Media', 'nivel_orden' => 2, 'sla_horas' => 24, 'color_badge' => 'primary'],
            ['nombre' => 'Alta', 'nivel_orden' => 3, 'sla_horas' => 8, 'color_badge' => 'warning'],
            ['nombre' => 'Crítica', 'nivel_orden' => 4, 'sla_horas' => 2, 'color_badge' => 'danger'],
        ];

        foreach ($prioridades as $prioridad) {
            Prioridad::updateOrCreate(['nombre' => $prioridad['nombre']], $prioridad);
        }

        /*
         *
         *
         */
        $estados = [
            1 => [
                'nombre' => 'Abierto',
                'descripcion' => 'Ticket registrado por el usuario solicitante, pendiente de asignación',
                'es_cerrado' => false,
                'color_badge' => 'danger',
            ],
            2 => [
                'nombre' => 'Asignado',
                'descripcion' => 'Ticket revisado por el supervisor y derivado a un técnico de soporte',
                'es_cerrado' => false,
                'color_badge' => 'info',
            ],
            3 => [
                'nombre' => 'En Proceso',
                'descripcion' => 'El técnico se encuentra diagnosticando o aplicando procedimientos',
                'es_cerrado' => false,
                'color_badge' => 'warning',
            ],
            4 => [
                'nombre' => 'Resuelto',
                'descripcion' => 'El técnico registró el diagnóstico y aplicó la solución a la incidencia',
                'es_cerrado' => true,
                'color_badge' => 'success',
            ],
            5 => [
                'nombre' => 'Cerrado',
                'descripcion' => 'Incidencia validada y archivada definitivamente en el historial',
                'es_cerrado' => true,
                'color_badge' => 'dark',
            ],
        ];

        foreach ($estados as $idEstado => $estado) {
            Estado::updateOrCreate(['id_estado' => $idEstado], $estado);
        }
    }
}
