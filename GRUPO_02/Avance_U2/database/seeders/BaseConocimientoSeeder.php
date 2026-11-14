<?php

namespace Database\Seeders;

use App\Models\BaseConocimiento;
use App\Models\Categoria;
use App\Models\Servicio;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class BaseConocimientoSeeder extends Seeder
{
    public function run(): void
    {
        $servicios = Servicio::pluck('id_servicio', 'codigo_servicio');
        $categorias = Categoria::pluck('id_categoria', 'nombre');
        $autores = Usuario::pluck('id_usuario', 'email');

        $articulos = [
            [
                'codigo_articulo' => 'KB-2026-001',
                'servicio' => 'SRV-SIS',
                'categoria' => 'Acceso y Credenciales Incorrectas',
                'autor' => 'tecnico1.demo@demo.test',
                'tipo_documento' => 'Procedimiento',
                'titulo' => 'Restablecimiento de acceso y desbloqueo en el Sistema Académico',
                'sintomas_problema' => 'El usuario reporta que no puede acceder al sistema académico y al ingresar usuario y contraseña aparece un mensaje indicando que las credenciales son incorrectas.',
                'pasos_solucion' => "1. Ingresar al módulo de Seguridad del Sistema Académico (Tabla SEG_USUARIOS).\n2. Verificar si el campo INTENTOS_FALLIDOS es mayor o igual a 3 (bloqueo preventivo).\n3. Reiniciar el contador de intentos a 0 y generar un token temporal de restablecimiento.\n4. Solicitar al usuario limpiar caché del navegador (Ctrl + F5) y cambiar su clave en el primer inicio.",
                'palabras_clave' => 'sistema academico credenciales incorrectas usuario contrasena acceso bloqueo',
            ],
            [
                'codigo_articulo' => 'KB-2026-002',
                'servicio' => 'SRV-COR',
                'categoria' => 'Sincronización y Bloqueo de Correo',
                'autor' => 'tecnico1.demo@demo.test',
                'tipo_documento' => 'Procedimiento',
                'titulo' => 'Procedimiento de desbloqueo y recuperación de Correo Institucional',
                'sintomas_problema' => 'El usuario no puede acceder al correo institucional por bloqueo de seguridad, intentos fallidos o desconfiguración del cliente de correo.',
                'pasos_solucion' => "1. Acceder al panel de administración de identidades institucionales.\n2. Comprobar si la cuenta presenta bloqueo por inicio de sesión desde ubicación inusual.\n3. Desbloquear cuenta, restablecer contraseña temporal y verificar licencia activa.\n4. Si usa cliente Outlook/Thunderbird, validar puertos IMAP (993 SSL) y SMTP (587 TLS).",
                'palabras_clave' => 'correo institucional acceso bloqueo cuenta restablecimiento contrasena cliente correo',
            ],
            [
                'codigo_articulo' => 'KB-2026-003',
                'servicio' => 'SRV-RED',
                'categoria' => 'Caída de Conectividad / Sin Internet',
                'autor' => 'tecnico2.demo@demo.test',
                'tipo_documento' => 'Manual',
                'titulo' => 'Diagnóstico de pérdida de conectividad de red en oficinas y laboratorios',
                'sintomas_problema' => 'El equipo informático muestra ícono de red sin acceso a internet o no obtiene dirección IP válida.',
                'pasos_solucion' => "1. Verificar estado de luces de enlace (Link/Act) en la roseta RJ45 y en el switch de distribución.\n2. Ejecutar ipconfig /release e ipconfig /renew para validar respuesta del servidor DHCP.\n3. Comprobar que el puerto del switch esté asignado a la VLAN correspondiente.",
                'palabras_clave' => 'red conectividad internet wifi dhcp ip switch laboratorio',
            ],
            [
                'codigo_articulo' => 'KB-2026-004',
                'servicio' => 'SRV-IMP',
                'categoria' => 'Atasco o Desconfiguración de Impresora',
                'autor' => 'tecnico2.demo@demo.test',
                'tipo_documento' => 'FAQ',
                'titulo' => 'Solución a cola de impresión atascada en impresoras multifuncionales de red',
                'sintomas_problema' => 'Los documentos enviados a la impresora departamental se quedan en estado Imprimiendo y no salen.',
                'pasos_solucion' => "1. En el equipo afectado, abrir services.msc y detener el servicio Spooler (Cola de impresión).\n2. Eliminar los archivos temporales dentro de C:\\Windows\\System32\\spool\\PRINTERS.\n3. Iniciar nuevamente el servicio Spooler y verificar ping hacia la IP fija de la impresora.",
                'palabras_clave' => 'impresora red cola impresion atascada spooler driver',
            ],
        ];

        foreach ($articulos as $articulo) {
            BaseConocimiento::updateOrCreate(
                ['codigo_articulo' => $articulo['codigo_articulo']],
                [
                    'id_servicio' => $servicios[$articulo['servicio']],
                    'id_categoria' => $categorias[$articulo['categoria']],
                    'id_autor' => $autores[$articulo['autor']],
                    'tipo_documento' => $articulo['tipo_documento'],
                    'titulo' => $articulo['titulo'],
                    'sintomas_problema' => $articulo['sintomas_problema'],
                    'pasos_solucion' => $articulo['pasos_solucion'],
                    'palabras_clave' => $articulo['palabras_clave'],
                    'activo' => true,
                ]
            );
        }
    }
}
