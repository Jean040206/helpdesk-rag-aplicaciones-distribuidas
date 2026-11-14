CREATE DATABASE IF NOT EXISTS `helpdesk_rag_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `helpdesk_rag_db`;
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `asistente_rag_evaluaciones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asistente_rag_evaluaciones` (
  `id_evaluacion_rag` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_ticket` int(10) unsigned NOT NULL,
  `id_articulo_recuperado` int(10) unsigned DEFAULT NULL,
  `id_tecnico_evaluador` int(10) unsigned DEFAULT NULL,
  `metodo_ia` enum('Recuperacion_KB','LLM_Sin_Contexto','Asistente_RAG') NOT NULL DEFAULT 'Asistente_RAG',
  `proveedor` varchar(40) DEFAULT NULL,
  `modelo_utilizado` varchar(80) DEFAULT NULL,
  `categoria_sugerida` varchar(100) DEFAULT NULL,
  `prioridad_sugerida` varchar(40) DEFAULT NULL,
  `servicio_sugerido` varchar(100) DEFAULT NULL,
  `posible_causa` text DEFAULT NULL,
  `solucion_recomendada` text DEFAULT NULL,
  `decision_tecnico` enum('Pendiente','Aceptada','Modificada','Descartada') NOT NULL DEFAULT 'Pendiente',
  `solucion_final_tecnico` text DEFAULT NULL,
  `tiempo_procesamiento_ms` int(11) NOT NULL DEFAULT 0,
  `es_dato_sintetico` tinyint(1) NOT NULL DEFAULT 0,
  `prompt_enviado` longtext DEFAULT NULL,
  `respuesta_cruda` longtext DEFAULT NULL,
  `respuesta_estructurada` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`respuesta_estructurada`)),
  `estado` varchar(20) NOT NULL DEFAULT 'pendiente',
  `error_mensaje` text DEFAULT NULL,
  `tokens_entrada` int(11) DEFAULT NULL,
  `tokens_salida` int(11) DEFAULT NULL,
  `parametros` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`parametros`)),
  `confianza` decimal(5,2) DEFAULT NULL,
  `fecha_evento` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_decision` timestamp NULL DEFAULT NULL,
  `comentario_tecnico` text DEFAULT NULL,
  PRIMARY KEY (`id_evaluacion_rag`),
  KEY `fk_rag_articulo` (`id_articulo_recuperado`),
  KEY `fk_rag_tecnico` (`id_tecnico_evaluador`),
  KEY `idx_rag_ticket_estado` (`id_ticket`,`estado`),
  CONSTRAINT `fk_rag_articulo` FOREIGN KEY (`id_articulo_recuperado`) REFERENCES `base_conocimiento` (`id_articulo`) ON DELETE SET NULL,
  CONSTRAINT `fk_rag_tecnico` FOREIGN KEY (`id_tecnico_evaluador`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL,
  CONSTRAINT `fk_rag_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_ticket`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `asistente_rag_fuentes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `asistente_rag_fuentes` (
  `id_fuente` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_evaluacion_rag` int(10) unsigned NOT NULL,
  `id_articulo` int(10) unsigned NOT NULL,
  `posicion` int(11) NOT NULL,
  `score` decimal(8,4) DEFAULT NULL,
  `fragmento` text DEFAULT NULL,
  `fecha_recuperacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_fuente`),
  UNIQUE KEY `uq_rag_fuente_articulo` (`id_evaluacion_rag`,`id_articulo`),
  KEY `fk_rag_fuente_articulo` (`id_articulo`),
  CONSTRAINT `fk_rag_fuente_articulo` FOREIGN KEY (`id_articulo`) REFERENCES `base_conocimiento` (`id_articulo`) ON DELETE CASCADE,
  CONSTRAINT `fk_rag_fuente_evaluacion` FOREIGN KEY (`id_evaluacion_rag`) REFERENCES `asistente_rag_evaluaciones` (`id_evaluacion_rag`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `auditoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `auditoria` (
  `id_auditoria` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` int(10) unsigned DEFAULT NULL,
  `modulo` varchar(60) NOT NULL,
  `accion` varchar(80) NOT NULL,
  `descripcion_evento` text NOT NULL,
  `ip_origen` varchar(45) NOT NULL DEFAULT '127.0.0.1',
  `user_agent` varchar(255) DEFAULT NULL,
  `fecha_evento` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_auditoria`),
  KEY `fk_auditoria_usuario` (`id_usuario`),
  KEY `idx_auditoria_modulo_accion` (`modulo`,`accion`),
  KEY `idx_auditoria_fecha` (`fecha_evento`),
  CONSTRAINT `fk_auditoria_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `base_conocimiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `base_conocimiento` (
  `id_articulo` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_articulo` varchar(20) NOT NULL,
  `id_servicio` int(10) unsigned NOT NULL,
  `id_categoria` int(10) unsigned NOT NULL,
  `id_autor` int(10) unsigned NOT NULL,
  `tipo_documento` enum('Manual','Procedimiento','FAQ','Solucion_Anterior','Doc_Tecnica') NOT NULL,
  `titulo` varchar(180) NOT NULL,
  `sintomas_problema` text NOT NULL,
  `pasos_solucion` text NOT NULL,
  `palabras_clave` varchar(200) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_publicacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_articulo`),
  UNIQUE KEY `base_conocimiento_codigo_articulo_unique` (`codigo_articulo`),
  KEY `fk_kb_servicio` (`id_servicio`),
  KEY `fk_kb_categoria` (`id_categoria`),
  KEY `fk_kb_autor` (`id_autor`),
  FULLTEXT KEY `idx_kb_busqueda_rag` (`titulo`,`sintomas_problema`,`pasos_solucion`,`palabras_clave`),
  CONSTRAINT `fk_kb_autor` FOREIGN KEY (`id_autor`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_kb_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON UPDATE CASCADE,
  CONSTRAINT `fk_kb_servicio` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id_categoria` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_servicio_default` int(10) unsigned DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(200) NOT NULL,
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `categorias_nombre_unique` (`nombre`),
  KEY `fk_categoria_servicio` (`id_servicio_default`),
  CONSTRAINT `fk_categoria_servicio` FOREIGN KEY (`id_servicio_default`) REFERENCES `servicios` (`id_servicio`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `estados`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `estados` (
  `id_estado` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(40) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `es_cerrado` tinyint(1) NOT NULL DEFAULT 0,
  `color_badge` varchar(30) NOT NULL DEFAULT 'primary',
  PRIMARY KEY (`id_estado`),
  UNIQUE KEY `estados_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `prioridades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `prioridades` (
  `id_prioridad` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(40) NOT NULL,
  `nivel_orden` int(11) NOT NULL,
  `sla_horas` int(11) NOT NULL DEFAULT 24,
  `color_badge` varchar(30) NOT NULL DEFAULT 'secondary',
  PRIMARY KEY (`id_prioridad`),
  UNIQUE KEY `prioridades_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id_rol` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(200) NOT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `roles_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `servicios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `servicios` (
  `id_servicio` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_servicio` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_servicio`),
  UNIQUE KEY `servicios_codigo_servicio_unique` (`codigo_servicio`),
  UNIQUE KEY `servicios_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ticket_evidencias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_evidencias` (
  `id_evidencia` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_ticket` int(10) unsigned NOT NULL,
  `nombre_original` varchar(180) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `extension` varchar(20) NOT NULL,
  `tamano_kb` int(11) NOT NULL DEFAULT 0,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_evidencia`),
  KEY `fk_evidencia_ticket` (`id_ticket`),
  CONSTRAINT `fk_evidencia_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_ticket`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ticket_historial`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_historial` (
  `id_historial` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_ticket` int(10) unsigned NOT NULL,
  `id_estado_anterior` int(10) unsigned DEFAULT NULL,
  `id_estado_nuevo` int(10) unsigned NOT NULL,
  `id_usuario_responsable` int(10) unsigned NOT NULL,
  `observacion` varchar(255) NOT NULL,
  `fecha_cambio` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_historial`),
  KEY `fk_hist_est_ant` (`id_estado_anterior`),
  KEY `fk_hist_est_nue` (`id_estado_nuevo`),
  KEY `fk_hist_usuario` (`id_usuario_responsable`),
  KEY `idx_hist_ticket_fecha` (`id_ticket`,`fecha_cambio`),
  CONSTRAINT `fk_hist_est_ant` FOREIGN KEY (`id_estado_anterior`) REFERENCES `estados` (`id_estado`),
  CONSTRAINT `fk_hist_est_nue` FOREIGN KEY (`id_estado_nuevo`) REFERENCES `estados` (`id_estado`),
  CONSTRAINT `fk_hist_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_ticket`) ON DELETE CASCADE,
  CONSTRAINT `fk_hist_usuario` FOREIGN KEY (`id_usuario_responsable`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `ticket_seguimiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `ticket_seguimiento` (
  `id_seguimiento` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_ticket` int(10) unsigned NOT NULL,
  `id_usuario` int(10) unsigned NOT NULL,
  `comentario` text NOT NULL,
  `tipo_registro` enum('Comentario','Diagnostico','Solucion') NOT NULL DEFAULT 'Comentario',
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_seguimiento`),
  KEY `fk_seguimiento_ticket` (`id_ticket`),
  KEY `fk_seguimiento_usuario` (`id_usuario`),
  CONSTRAINT `fk_seguimiento_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_ticket`) ON DELETE CASCADE,
  CONSTRAINT `fk_seguimiento_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id_ticket` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `codigo_ticket` varchar(25) NOT NULL,
  `id_solicitante` int(10) unsigned NOT NULL,
  `id_tecnico_asignado` int(10) unsigned DEFAULT NULL,
  `id_supervisor_asignador` int(10) unsigned DEFAULT NULL,
  `id_servicio` int(10) unsigned NOT NULL,
  `id_categoria` int(10) unsigned NOT NULL,
  `id_prioridad` int(10) unsigned NOT NULL,
  `id_estado` int(10) unsigned NOT NULL DEFAULT 1,
  `asunto` varchar(180) NOT NULL,
  `descripcion` text NOT NULL,
  `diagnostico_tecnico` text DEFAULT NULL,
  `solucion_aplicada` text DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_asignacion` datetime DEFAULT NULL,
  `fecha_resolucion` datetime DEFAULT NULL,
  PRIMARY KEY (`id_ticket`),
  UNIQUE KEY `tickets_codigo_ticket_unique` (`codigo_ticket`),
  KEY `fk_ticket_tecnico` (`id_tecnico_asignado`),
  KEY `fk_ticket_supervisor` (`id_supervisor_asignador`),
  KEY `fk_ticket_servicio` (`id_servicio`),
  KEY `fk_ticket_categoria` (`id_categoria`),
  KEY `fk_ticket_prioridad` (`id_prioridad`),
  KEY `idx_tickets_estado_prioridad` (`id_estado`,`id_prioridad`),
  KEY `idx_tickets_solicitante_fecha` (`id_solicitante`,`fecha_registro`),
  CONSTRAINT `fk_ticket_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id_categoria`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticket_estado` FOREIGN KEY (`id_estado`) REFERENCES `estados` (`id_estado`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticket_prioridad` FOREIGN KEY (`id_prioridad`) REFERENCES `prioridades` (`id_prioridad`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticket_servicio` FOREIGN KEY (`id_servicio`) REFERENCES `servicios` (`id_servicio`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticket_solicitante` FOREIGN KEY (`id_solicitante`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticket_supervisor` FOREIGN KEY (`id_supervisor_asignador`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticket_tecnico` FOREIGN KEY (`id_tecnico_asignado`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id_usuario` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_rol` int(10) unsigned NOT NULL,
  `codigo_institucional` varchar(20) NOT NULL,
  `nombre_completo` varchar(120) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `dependencia_oficina` varchar(100) NOT NULL,
  `especialidad_ti` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `remember_token` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `usuarios_codigo_institucional_unique` (`codigo_institucional`),
  UNIQUE KEY `usuarios_email_unique` (`email`),
  KEY `fk_usuarios_rol` (`id_rol`),
  CONSTRAINT `fk_usuarios_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;


INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
    (1, '2026_11_09_000001_create_catalogos_tables', 1),
    (2, '2026_11_09_000002_create_usuarios_table', 1),
    (3, '2026_11_09_000003_create_tickets_table', 1),
    (4, '2026_11_09_000004_create_ticket_related_tables', 1),
    (5, '2026_11_09_000005_create_base_conocimiento_table', 1),
    (6, '2026_11_09_000006_create_asistente_rag_tables', 1),
    (7, '2026_11_09_000007_create_auditoria_table', 1);
