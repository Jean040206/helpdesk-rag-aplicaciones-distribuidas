# HelpDesk RAG

Proyecto desarrollado para el curso de Aplicaciones Distribuidas I.

## Descripción

HelpDesk RAG es una aplicación web orientada a la gestión de tickets de soporte TI.

Durante el primer avance se desarrollará un prototipo funcional que permita registrar y consultar tickets utilizando una base de datos MySQL.

En los siguientes avances se incorporará progresivamente una base de conocimiento y un mecanismo de inteligencia artificial basado en Retrieval-Augmented Generation (RAG).

## Tecnologías

- PHP
- HTML5
- CSS3
- JavaScript
- MySQL
- Git / GitHub

## Funcionalidades del primer avance

El prototipo del Avance 01 deberá permitir:

- Navegar por las pantallas principales.
- Acceder a la pantalla de inicio o panel.
- Visualizar la pantalla de login.
- Registrar un nuevo ticket.
- Guardar el ticket en MySQL.
- Consultar los tickets registrados.
- Visualizar el detalle de un ticket.
- Acceder a la pantalla inicial de base de conocimiento.

## Pantallas principales

- Inicio / Panel
- Login
- Nuevo Ticket
- Lista de Tickets
- Detalle de Ticket
- Base de Conocimiento

## Base de datos

Las tablas principales definidas para el prototipo son:

- usuarios
- categorias
- tickets
- comentarios
- documentos_conocimiento

## Organización del código

El código correspondiente al primer avance se encuentra dentro de:

`GRUPO_02/Avance_01/`

La estructura será organizada de la siguiente manera:

- `src/` → código PHP del prototipo.
- `assets/` → archivos CSS, JavaScript e imágenes utilizadas por la interfaz.
- `config/` → configuración y conexión con MySQL.
- `database/` → script SQL de creación de la base de datos.

## Ejecución

Las instrucciones definitivas de instalación y ejecución se actualizarán cuando se complete la integración entre la interfaz web y MySQL.

Para el primer avance se utilizará un entorno local con PHP y MySQL.

## Estado del proyecto

Primer avance - Semana 6.

Actualmente el objetivo principal es disponer de un prototipo navegable conectado a MySQL que permita registrar y consultar tickets.
