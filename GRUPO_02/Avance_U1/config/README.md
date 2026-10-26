# Configuración de Base de Datos – HelpDesk RAG

## Descripción

Esta carpeta contiene la configuración de conexión entre PHP y MySQL del proyecto HelpDesk RAG, desarrollado por el Grupo 02 para el curso Aplicaciones Distribuidas I.

## Archivo database.php

El archivo `database.php` implementa una conexión centralizada a MySQL mediante PDO (PHP Data Objects).

La función principal es `conectarBD()`, que devuelve un objeto PDO para realizar operaciones con la base de datos.

## Requisitos

- XAMPP con Apache y MySQL activos.
- PHP con la extensión PDO MySQL habilitada.
- Base de datos `helpdesk_rag_db` importada correctamente.

## Configuración local

Para el entorno de demostración con XAMPP se utilizan los siguientes valores:

- Servidor: `127.0.0.1`
- Base de datos: `helpdesk_rag_db`
- Usuario: `root`
- Contraseña: vacía (configuración local de demostración).
- Codificación: `utf8mb4`

No se deben publicar credenciales reales en GitHub.

## Instalación y comprobación

1. Iniciar Apache y MySQL desde XAMPP.
2. Abrir `http://localhost/phpmyadmin/`.
3. Importar `database/DataBase.sql` para crear la base de datos.
4. Verificar que exista `helpdesk_rag_db` con sus tablas.
5. Comprobar que los parámetros de `config/database.php` coincidan con el entorno local.

## Estado de implementación

La función `conectarBD()` fue comprobada exitosamente mediante una prueba local de conexión PHP–MySQL.

Actualmente, las páginas web del prototipo utilizan `src/config/db.php`, que incluye conexión, gestión de sesiones y auditoría.

La integración de la conexión centralizada `config/database.php` con esas páginas queda pendiente de coordinación con el responsable de la interfaz.