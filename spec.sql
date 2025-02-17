-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 17-02-2025 a las 22:29:53
-- Versión del servidor: 8.0.31
-- Versión de PHP: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `spec`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alertas`
--

DROP TABLE IF EXISTS `alertas`;
CREATE TABLE IF NOT EXISTS `alertas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_alerta` enum('recordatorio','retraso','emergencia','finalizacion') NOT NULL,
  `ronda_id` int DEFAULT NULL,
  `vigilador_id` int DEFAULT NULL,
  `estado` enum('pendiente','atendida') DEFAULT 'pendiente',
  `fecha_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ronda_id` (`ronda_id`),
  KEY `vigilador_id` (`vigilador_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaciones_puestos`
--

DROP TABLE IF EXISTS `asignaciones_puestos`;
CREATE TABLE IF NOT EXISTS `asignaciones_puestos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `vigilador_id` int DEFAULT NULL,
  `puesto_id` int DEFAULT NULL,
  `estado` enum('en_servicio','guardia_pasiva','franco') NOT NULL,
  `fecha_asignacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vigilador_id` (`vigilador_id`),
  KEY `puesto_id` (`puesto_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `escaneos`
--

DROP TABLE IF EXISTS `escaneos`;
CREATE TABLE IF NOT EXISTS `escaneos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ronda_id` int DEFAULT NULL,
  `sector_id` int DEFAULT NULL,
  `vigilador_id` int DEFAULT NULL,
  `fecha_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `ronda_id` (`ronda_id`),
  KEY `sector_id` (`sector_id`),
  KEY `vigilador_id` (`vigilador_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `legajos`
--

DROP TABLE IF EXISTS `legajos`;
CREATE TABLE IF NOT EXISTS `legajos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `archivo` varchar(255) NOT NULL,
  `fecha_subida` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

DROP TABLE IF EXISTS `mensajes`;
CREATE TABLE IF NOT EXISTS `mensajes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `remitente_id` int DEFAULT NULL,
  `destinatario_id` int DEFAULT NULL,
  `contenido` text NOT NULL,
  `fecha_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `remitente_id` (`remitente_id`),
  KEY `destinatario_id` (`destinatario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puestos`
--

DROP TABLE IF EXISTS `puestos`;
CREATE TABLE IF NOT EXISTS `puestos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('fijo','movil','eventual') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rondas`
--

DROP TABLE IF EXISTS `rondas`;
CREATE TABLE IF NOT EXISTS `rondas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('fija','eventual') NOT NULL,
  `supervisor_id` int DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `supervisor_id` (`supervisor_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ronda_detalle`
--

DROP TABLE IF EXISTS `ronda_detalle`;
CREATE TABLE IF NOT EXISTS `ronda_detalle` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ronda_id` int DEFAULT NULL,
  `sector_id` int DEFAULT NULL,
  `orden` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ronda_id` (`ronda_id`),
  KEY `sector_id` (`sector_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sectores`
--

DROP TABLE IF EXISTS `sectores`;
CREATE TABLE IF NOT EXISTS `sectores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `qr_code` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `qr_code` (`qr_code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `dni` varchar(15) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono_emergencia` varchar(20) DEFAULT NULL,
  `rol` enum('administrador','supervisor','vigilador') NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dni` (`dni`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visitas`
--

DROP TABLE IF EXISTS `visitas`;
CREATE TABLE IF NOT EXISTS `visitas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `dni` varchar(15) NOT NULL,
  `patente` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `hora_llegada` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `hora_salida` timestamp NULL DEFAULT NULL,
  `domicilio_id` int DEFAULT NULL,
  `vigilador_id` int DEFAULT NULL,
  `estado` enum('pendiente','aprobada','rechazada') NOT NULL,
  PRIMARY KEY (`id`),
  KEY `domicilio_id` (`domicilio_id`),
  KEY `vigilador_id` (`vigilador_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
