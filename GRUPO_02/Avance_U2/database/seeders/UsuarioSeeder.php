<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Rol::pluck('id_rol', 'nombre');

        $usuarios = [
            [
                'codigo_institucional' => 'DOC-2026-01',
                'nombre_completo' => 'Docente Demo (dato sintético)',
                'email' => 'docente.demo@demo.test',
                'rol' => 'Solicitante',
                'dependencia_oficina' => 'Facultad de Ingeniería',
                'especialidad_ti' => null,
            ],
            [
                'codigo_institucional' => 'ADM-2026-02',
                'nombre_completo' => 'Registro Demo (dato sintético)',
                'email' => 'registro.demo@demo.test',
                'rol' => 'Solicitante',
                'dependencia_oficina' => 'Dirección de Evaluación y Registro',
                'especialidad_ti' => null,
            ],
            [
                'codigo_institucional' => 'SUP-2026-01',
                'nombre_completo' => 'Supervisor Demo (dato sintético)',
                'email' => 'supervisor.demo@demo.test',
                'rol' => 'Supervisor',
                'dependencia_oficina' => 'Oficina de Tecnologías de Información',
                'especialidad_ti' => 'Gestión de Servicios TI - ITIL',
            ],
            [
                'codigo_institucional' => 'TEC-2026-01',
                'nombre_completo' => 'Técnico Demo Uno (dato sintético)',
                'email' => 'tecnico1.demo@demo.test',
                'rol' => 'Tecnico',
                'dependencia_oficina' => 'Soporte de Sistemas y Cuentas',
                'especialidad_ti' => 'Sistemas Institucionales y Correo',
            ],
            [
                'codigo_institucional' => 'TEC-2026-02',
                'nombre_completo' => 'Técnico Demo Dos (dato sintético)',
                'email' => 'tecnico2.demo@demo.test',
                'rol' => 'Tecnico',
                'dependencia_oficina' => 'Unidad de Redes y Hardware',
                'especialidad_ti' => 'Redes, Conectividad e Impresoras',
            ],
            [
                'codigo_institucional' => 'ADM-2026-99',
                'nombre_completo' => 'Administrador Demo (dato sintético)',
                'email' => 'admin.demo@demo.test',
                'rol' => 'Administrador',
                'dependencia_oficina' => 'Oficina Central de TI',
                'especialidad_ti' => 'Administración de Plataforma',
            ],
        ];

        foreach ($usuarios as $usuario) {
            Usuario::updateOrCreate(
                ['codigo_institucional' => $usuario['codigo_institucional']],
                [
                    'id_rol' => $roles[$usuario['rol']],
                    'nombre_completo' => $usuario['nombre_completo'],
                    'email' => $usuario['email'],
                    'password_hash' => Hash::make('123456'),
                    'dependencia_oficina' => $usuario['dependencia_oficina'],
                    'especialidad_ti' => $usuario['especialidad_ti'],
                    'activo' => true,
                ]
            );
        }
    }
}
