DROP DATABASE IF EXISTS helpdesk_rag_db;
CREATE DATABASE helpdesk_rag_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE helpdesk_rag_db;

-- roles del sistema
CREATE TABLE roles (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(200) NOT NULL
) ENGINE=InnoDB;

-- usuarios: solicitantes, supervisores, técnicos y administradores
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    codigo_institucional VARCHAR(20) NOT NULL UNIQUE,
    nombre_completo VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    dependencia_oficina VARCHAR(100) NOT NULL,
    especialidad_ti VARCHAR(100) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_rol FOREIGN KEY (id_rol) REFERENCES roles(id_rol)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- catálogo de servicios de TI
CREATE TABLE servicios (
    id_servicio INT AUTO_INCREMENT PRIMARY KEY,
    codigo_servicio VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(255) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- categorías de incidencias (cada una con un servicio por defecto)
CREATE TABLE categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    id_servicio_default INT NULL,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(200) NOT NULL,
    CONSTRAINT fk_categoria_servicio FOREIGN KEY (id_servicio_default) REFERENCES servicios(id_servicio)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- prioridades y su tiempo de atención (SLA) en horas
CREATE TABLE prioridades (
    id_prioridad INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(40) NOT NULL UNIQUE,
    nivel_orden INT NOT NULL,
    sla_horas INT NOT NULL DEFAULT 24,
    color_badge VARCHAR(30) NOT NULL DEFAULT 'secondary'
) ENGINE=InnoDB;

-- estados por los que pasa un ticket
CREATE TABLE estados (
    id_estado INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(40) NOT NULL UNIQUE,
    descripcion VARCHAR(150) NOT NULL,
    es_cerrado TINYINT(1) NOT NULL DEFAULT 0,
    color_badge VARCHAR(30) NOT NULL DEFAULT 'primary'
) ENGINE=InnoDB;

-- tabla principal: un registro por cada ticket
CREATE TABLE tickets (
    id_ticket INT AUTO_INCREMENT PRIMARY KEY,
    codigo_ticket VARCHAR(25) NOT NULL UNIQUE,
    id_solicitante INT NOT NULL,
    id_tecnico_asignado INT NULL,
    id_supervisor_asignador INT NULL,
    id_servicio INT NOT NULL,
    id_categoria INT NOT NULL,
    id_prioridad INT NOT NULL,
    id_estado INT NOT NULL DEFAULT 1,
    asunto VARCHAR(180) NOT NULL,
    descripcion TEXT NOT NULL,
    diagnostico_tecnico TEXT NULL,
    solucion_aplicada TEXT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_asignacion DATETIME NULL,
    fecha_resolucion DATETIME NULL,
    CONSTRAINT fk_ticket_solicitante FOREIGN KEY (id_solicitante) REFERENCES usuarios(id_usuario),
    CONSTRAINT fk_ticket_tecnico FOREIGN KEY (id_tecnico_asignado) REFERENCES usuarios(id_usuario),
    CONSTRAINT fk_ticket_supervisor FOREIGN KEY (id_supervisor_asignador) REFERENCES usuarios(id_usuario),
    CONSTRAINT fk_ticket_servicio FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio),
    CONSTRAINT fk_ticket_categoria FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria),
    CONSTRAINT fk_ticket_prioridad FOREIGN KEY (id_prioridad) REFERENCES prioridades(id_prioridad),
    CONSTRAINT fk_ticket_estado FOREIGN KEY (id_estado) REFERENCES estados(id_estado)
) ENGINE=InnoDB;

-- archivos que se adjuntan a un ticket
CREATE TABLE ticket_evidencias (
    id_evidencia INT AUTO_INCREMENT PRIMARY KEY,
    id_ticket INT NOT NULL,
    nombre_original VARCHAR(180) NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    extension VARCHAR(20) NOT NULL,
    tamano_kb INT NOT NULL DEFAULT 0,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_evidencia_ticket FOREIGN KEY (id_ticket) REFERENCES tickets(id_ticket)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- comentarios y diagnósticos que se agregan al ticket
CREATE TABLE ticket_seguimiento (
    id_seguimiento INT AUTO_INCREMENT PRIMARY KEY,
    id_ticket INT NOT NULL,
    id_usuario INT NOT NULL,
    comentario TEXT NOT NULL,
    tipo_registro ENUM('Comentario', 'Diagnostico', 'Solucion') NOT NULL DEFAULT 'Comentario',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_seguimiento_ticket FOREIGN KEY (id_ticket) REFERENCES tickets(id_ticket)
        ON DELETE CASCADE,
    CONSTRAINT fk_seguimiento_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- cambios de estado del ticket y quién los hizo
CREATE TABLE ticket_historial (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_ticket INT NOT NULL,
    id_estado_anterior INT NULL,
    id_estado_nuevo INT NOT NULL,
    id_usuario_responsable INT NOT NULL,
    observacion VARCHAR(255) NOT NULL,
    fecha_cambio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_hist_ticket FOREIGN KEY (id_ticket) REFERENCES tickets(id_ticket) ON DELETE CASCADE,
    CONSTRAINT fk_hist_est_ant FOREIGN KEY (id_estado_anterior) REFERENCES estados(id_estado),
    CONSTRAINT fk_hist_est_nue FOREIGN KEY (id_estado_nuevo) REFERENCES estados(id_estado),
    CONSTRAINT fk_hist_usuario FOREIGN KEY (id_usuario_responsable) REFERENCES usuarios(id_usuario)
) ENGINE=InnoDB;

-- manuales y soluciones anteriores (lo que consultará el asistente)
CREATE TABLE base_conocimiento (
    id_articulo INT AUTO_INCREMENT PRIMARY KEY,
    codigo_articulo VARCHAR(20) NOT NULL UNIQUE,
    id_servicio INT NOT NULL,
    id_categoria INT NOT NULL,
    id_autor INT NOT NULL,
    tipo_documento ENUM('Manual', 'Procedimiento', 'FAQ', 'Solucion_Anterior', 'Doc_Tecnica') NOT NULL,
    titulo VARCHAR(180) NOT NULL,
    sintomas_problema TEXT NOT NULL,
    pasos_solucion TEXT NOT NULL,
    palabras_clave VARCHAR(200) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_publicacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_kb_servicio FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio),
    CONSTRAINT fk_kb_categoria FOREIGN KEY (id_categoria) REFERENCES categorias(id_categoria),
    CONSTRAINT fk_kb_autor FOREIGN KEY (id_autor) REFERENCES usuarios(id_usuario),
    FULLTEXT KEY idx_kb_busqueda_rag (titulo, sintomas_problema, pasos_solucion, palabras_clave)
) ENGINE=InnoDB;

-- recomendaciones del asistente y si el técnico las aceptó (se usa desde el hito 2)
CREATE TABLE asistente_rag_evaluaciones (
    id_evaluacion_rag INT AUTO_INCREMENT PRIMARY KEY,
    id_ticket INT NOT NULL,
    id_articulo_recuperado INT NULL,
    id_tecnico_evaluador INT NULL,
    metodo_ia ENUM('Recuperacion_KB', 'LLM_Sin_Contexto', 'Asistente_RAG') NOT NULL DEFAULT 'Asistente_RAG',
    modelo_utilizado VARCHAR(80) NOT NULL DEFAULT 'Pendiente_Hito2',
    categoria_sugerida VARCHAR(100) NULL,
    prioridad_sugerida VARCHAR(40) NULL,
    servicio_sugerido VARCHAR(100) NULL,
    posible_causa TEXT NULL,
    solucion_recomendada TEXT NOT NULL,
    decision_tecnico ENUM('Pendiente', 'Aceptada', 'Modificada', 'Descartada') NOT NULL DEFAULT 'Pendiente',
    solucion_final_tecnico TEXT NULL,
    tiempo_procesamiento_ms INT NOT NULL DEFAULT 0,
    es_dato_sintetico TINYINT(1) NOT NULL DEFAULT 1,
    fecha_evento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rag_ticket FOREIGN KEY (id_ticket) REFERENCES tickets(id_ticket) ON DELETE CASCADE,
    CONSTRAINT fk_rag_articulo FOREIGN KEY (id_articulo_recuperado) REFERENCES base_conocimiento(id_articulo) ON DELETE SET NULL,
    CONSTRAINT fk_rag_tecnico FOREIGN KEY (id_tecnico_evaluador) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB;

-- registro de acciones importantes del sistema
CREATE TABLE auditoria (
    id_auditoria INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NULL,
    modulo VARCHAR(60) NOT NULL,
    accion VARCHAR(80) NOT NULL,
    descripcion_evento TEXT NOT NULL,
    ip_origen VARCHAR(45) NOT NULL DEFAULT '127.0.0.1',
    fecha_evento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_auditoria_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Datos de prueba (con nombres reales)

INSERT INTO roles (nombre, descripcion) VALUES
('Solicitante', 'Usuario institucional que registra tickets de soporte y adjunta evidencias'),
('Supervisor', 'Revisa las incidencias entrantes y las asigna a los técnicos correspondientes'),
('Tecnico', 'Visualiza tickets asignados, consulta base de conocimiento/RAG y registra soluciones'),
('Administrador', 'Gestiona usuarios, roles, catálogo de servicios, categorías y auditoría');

-- todos los usuarios tienen la clave 123456 (guardada con hash bcrypt)
INSERT INTO usuarios (id_rol, codigo_institucional, nombre_completo, email, password_hash, dependencia_oficina, especialidad_ti) VALUES
(1, 'DOC-2026-01', 'Luis Chavez', 'luis.chavez@demo.test', '$2y$10$FBTRs6TCkrU7.QJxB8S9hOJmo6MYiGYy7CVAXBCXUgKBtTLnFt7Ki', 'Facultad de Ingeniería', NULL),
(1, 'ADM-2026-02', 'Sander Olortigue', 'sander.olortigue@demo.test', '$2y$10$FBTRs6TCkrU7.QJxB8S9hOJmo6MYiGYy7CVAXBCXUgKBtTLnFt7Ki', 'Dirección de Evaluación y Registro', NULL),
(2, 'SUP-2026-01', 'Dantin Prado', 'dantin.prado@demo.test', '$2y$10$FBTRs6TCkrU7.QJxB8S9hOJmo6MYiGYy7CVAXBCXUgKBtTLnFt7Ki', 'Oficina de Tecnologías de Información', 'Gestión de Servicios TI - ITIL'),
(3, 'TEC-2026-01', 'Walter Flores', 'walter.flores@demo.test', '$2y$10$FBTRs6TCkrU7.QJxB8S9hOJmo6MYiGYy7CVAXBCXUgKBtTLnFt7Ki', 'Soporte de Sistemas y Cuentas', 'Sistemas Institucionales y Correo'),
(3, 'TEC-2026-02', 'Yampier Salianas', 'yampier.salianas@demo.test', '$2y$10$FBTRs6TCkrU7.QJxB8S9hOJmo6MYiGYy7CVAXBCXUgKBtTLnFt7Ki', 'Unidad de Redes y Hardware', 'Redes, Conectividad e Impresoras'),
(4, 'ADM-2026-99', 'Michel Crisanto', 'michel.crisanto@demo.test', '$2y$10$FBTRs6TCkrU7.QJxB8S9hOJmo6MYiGYy7CVAXBCXUgKBtTLnFt7Ki', 'Oficina Central de TI', 'Administración de Plataforma');

INSERT INTO servicios (codigo_servicio, nombre, descripcion) VALUES
('SRV-SIS', 'Sistemas Institucionales', 'Sistema Académico, intranet docente, matrícula y gestión administrativa'),
('SRV-COR', 'Cuentas de Usuario y Correo', 'Correo institucional @uns.edu.pe, Active Directory y credenciales SSO'),
('SRV-RED', 'Redes y Conectividad', 'Red cableada, Wi-Fi de campus, switches, puntos de acceso y VPN'),
('SRV-EQU', 'Equipos Informáticos', 'Computadoras de escritorio, estaciones de laboratorio y laptops asignadas'),
('SRV-SW',  'Software y Licencias', 'Sistemas operativos, suites de ofimática, antivirus y software de ingeniería'),
('SRV-IMP', 'Impresoras y Periféricos', 'Impresoras multifuncionales en red, escáneres y proyectores multimedia');

INSERT INTO categorias (id_servicio_default, nombre, descripcion) VALUES
(1, 'Acceso y Credenciales Incorrectas', 'Bloqueo de cuenta, olvido de clave o error de autenticación en sistemas'),
(2, 'Sincronización y Bloqueo de Correo', 'Fallas de ingreso al correo institucional, buzón lleno o rebote SMTP'),
(3, 'Caída de Conectividad / Sin Internet', 'Pérdida de conexión de red local, IP conflictiva o falla de Wi-Fi'),
(4, 'Falla de Hardware / No Enciende', 'Averías físicas en fuente de poder, disco duro, memoria RAM o monitor'),
(5, 'Instalación o Error de Software', 'Cierre inesperado de programas, vencimiento de licencia o actualización'),
(6, 'Atasco o Desconfiguración de Impresora', 'Cola de impresión bloqueada, error de driver en red o falta de tóner');

INSERT INTO prioridades (nombre, nivel_orden, sla_horas, color_badge) VALUES
('Baja', 1, 48, 'info'),
('Media', 2, 24, 'primary'),
('Alta', 3, 8, 'warning'),
('Crítica', 4, 2, 'danger');

INSERT INTO estados (nombre, descripcion, es_cerrado, color_badge) VALUES
('Abierto', 'Ticket registrado por el usuario solicitante, pendiente de asignación', 0, 'danger'),
('Asignado', 'Ticket revisado por el supervisor y derivado a un técnico de soporte', 0, 'info'),
('En Proceso', 'El técnico se encuentra diagnosticando o aplicando procedimientos', 0, 'warning'),
('Resuelto', 'El técnico registró el diagnóstico y aplicó la solución a la incidencia', 1, 'success'),
('Cerrado', 'Incidencia validada y archivada definitivamente en el historial', 1, 'dark');

-- artículos de la base de conocimiento
INSERT INTO base_conocimiento (codigo_articulo, id_servicio, id_categoria, id_autor, tipo_documento, titulo, sintomas_problema, pasos_solucion, palabras_clave) VALUES
('KB-2026-001', 1, 1, 4, 'Procedimiento',
 'Restablecimiento de acceso y desbloqueo en el Sistema Académico',
 'El usuario reporta que no puede acceder al sistema académico y al ingresar usuario y contraseña aparece un mensaje indicando que las credenciales son incorrectas.',
 '1. Ingresar al módulo de Seguridad del Sistema Académico ( Tabla SEG_USUARIOS ).\n2. Verificar si el campo INTENTOS_FALLIDOS es mayor o igual a 3 (bloqueo preventivo).\n3. Reiniciar el contador de intentos a 0 y generar un token temporal de restablecimiento.\n4. Solicitar al usuario limpiar caché del navegador (Ctrl + F5) y cambiar su clave en el primer inicio.',
 'sistema academico credenciales incorrectas usuario contrasena acceso bloqueo'),

('KB-2026-002', 2, 2, 4, 'Procedimiento',
 'Procedimiento de desbloqueo y recuperación de Correo Institucional',
 'El usuario no puede acceder al correo institucional por bloqueo de seguridad, intentos fallidos o desconfiguración del cliente de correo.',
 '1. Acceder al panel de administración de identidades institucionales.\n2. Comprobar si la cuenta presenta bloqueo por inicio de sesión desde ubicación inusual.\n3. Desbloquear cuenta, restablecer contraseña temporal y verificar licencia activa.\n4. Si usa cliente Outlook/Thunderbird, validar puertos IMAP (993 SSL) y SMTP (587 TLS).',
 'correo institucional acceso bloqueo cuenta restablecimiento contrasena cliente correo'),

('KB-2026-003', 3, 3, 5, 'Manual',
 'Diagnóstico de pérdida de conectividad de red en oficinas y laboratorios',
 'El equipo informático muestra ícono de red sin acceso a internet o no obtiene dirección IP válida.',
 '1. Verificar estado de luces de enlace (Link/Act) en la roseta RJ45 y en el switch de distribución.\n2. Ejecutar ipconfig /release e ipconfig /renew para validar respuesta del servidor DHCP.\n3. Comprobar que el puerto del switch esté asignado a la VLAN correspondiente.',
 'red conectividad internet wifi dhcp ip switch laboratorio'),

('KB-2026-004', 6, 6, 5, 'FAQ',
 'Solución a cola de impresión atascada en impresoras multifuncionales de red',
 'Los documentos enviados a la impresora departamental se quedan en estado Imprimiendo y no salen.',
 '1. En el equipo afectado, abrir services.msc y detener el servicio Spooler (Cola de impresión).\n2. Eliminar los archivos temporales dentro de C:\\Windows\\System32\\spool\\PRINTERS.\n3. Iniciar nuevamente el servicio Spooler y verificar ping hacia la IP fija de la impresora.',
 'impresora red cola impresion atascada spooler driver');

-- tickets de ejemplo
INSERT INTO tickets (codigo_ticket, id_solicitante, id_tecnico_asignado, id_supervisor_asignador, id_servicio, id_categoria, id_prioridad, id_estado, asunto, descripcion, diagnostico_tecnico, fecha_asignacion) VALUES
('TKT-2026-0001', 1, 4, 3, 1, 1, 3, 2,
 'No puedo acceder al sistema académico',
 'No puedo acceder al sistema académico. Al ingresar mi usuario y contraseña aparece un mensaje indicando que las credenciales son incorrectas.',
 'Se verificó en el log de autenticación que la cuenta docente superó el límite de intentos tras el cambio de semestre.',
 NOW()),

('TKT-2026-0002', 2, 4, 3, 2, 2, 3, 3,
 'El usuario no puede acceder al correo institucional',
 'El usuario no puede acceder al correo institucional desde esta mañana. Solicito restablecimiento o revisión de bloqueo de cuenta.',
 'Cuenta bloqueada preventivamente por sincronización con contraseña antigua en dispositivo móvil.',
 NOW()),

('TKT-2026-0003', 1, NULL, NULL, 6, 6, 2, 1,
 'Impresora de red de escuela no imprime actas',
 'Al enviar documentos PDF a la impresora multifuncional de la oficina, los trabajos se quedan en cola y el equipo muestra error de conexión.',
 NULL,
 NULL);

INSERT INTO ticket_seguimiento (id_ticket, id_usuario, comentario, tipo_registro) VALUES
(1, 3, 'Ticket revisado por supervisión y asignado al técnico Walter Flores por corresponder a Sistemas Institucionales.', 'Comentario'),
(2, 4, 'Se consultó el procedimiento KB-2026-002 en la base de conocimiento para desbloqueo de cuenta institucional.', 'Diagnostico');

INSERT INTO ticket_historial (id_ticket, id_estado_anterior, id_estado_nuevo, id_usuario_responsable, observacion) VALUES
(1, NULL, 1, 1, 'Creación inicial del ticket por el usuario solicitante'),
(1, 1, 2, 3, 'Asignado al técnico Walter Flores por el supervisor'),
(2, NULL, 1, 2, 'Creación inicial del ticket por el usuario solicitante'),
(2, 1, 3, 4, 'Ticket en proceso de diagnóstico con apoyo de Base de Conocimiento'),
(3, NULL, 1, 1, 'Creación inicial del ticket por el usuario solicitante');

INSERT INTO auditoria (id_usuario, modulo, accion, descripcion_evento) VALUES
(1, 'Tickets', 'REGISTRO_TICKET', 'Usuario registró el ticket TKT-2026-0001 (Sistemas Institucionales)'),
(3, 'Supervisión', 'ASIGNAR_TECNICO', 'Supervisor asignó TKT-2026-0001 al técnico Walter Flores');
