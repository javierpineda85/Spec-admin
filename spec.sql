-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 04-05-2025 a las 19:40:56
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
  `idAlerta` int NOT NULL AUTO_INCREMENT,
  `tipo_alerta` enum('recordatorio','retraso','emergencia','finalizacion') NOT NULL,
  `ronda_id` int DEFAULT NULL,
  `vigilador_id` int DEFAULT NULL,
  `estado` enum('pendiente','atendida') DEFAULT 'pendiente',
  `fecha_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idAlerta`),
  KEY `ronda_id` (`ronda_id`),
  KEY `vigilador_id` (`vigilador_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaciones_objetivos`
--

DROP TABLE IF EXISTS `asignaciones_objetivos`;
CREATE TABLE IF NOT EXISTS `asignaciones_objetivos` (
  `idAsigna` int NOT NULL AUTO_INCREMENT,
  `vigilador_id` int DEFAULT NULL,
  `objetivo_id` int DEFAULT NULL,
  `estado` enum('en_servicio','guardia_pasiva','franco') NOT NULL,
  `fecha_asignacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idAsigna`),
  KEY `vigilador_id` (`vigilador_id`),
  KEY `puesto_id` (`objetivo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bajas`
--

DROP TABLE IF EXISTS `bajas`;
CREATE TABLE IF NOT EXISTS `bajas` (
  `idBaja` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `motivo` varchar(100) NOT NULL,
  `fecha` date NOT NULL,
  `eliminado_por` int NOT NULL,
  PRIMARY KEY (`idBaja`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cronogramas`
--

DROP TABLE IF EXISTS `cronogramas`;
CREATE TABLE IF NOT EXISTS `cronogramas` (
  `idCrono` int NOT NULL AUTO_INCREMENT,
  `objetivo_id` int NOT NULL,
  `imgCrono` varchar(100) NOT NULL,
  `fechaCarga` date NOT NULL,
  PRIMARY KEY (`idCrono`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `directivas`
--

DROP TABLE IF EXISTS `directivas`;
CREATE TABLE IF NOT EXISTS `directivas` (
  `idDirectiva` int NOT NULL AUTO_INCREMENT,
  `detalle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `id_objetivo` int NOT NULL,
  PRIMARY KEY (`idDirectiva`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `escaneos`
--

DROP TABLE IF EXISTS `escaneos`;
CREATE TABLE IF NOT EXISTS `escaneos` (
  `idEscaneo` int NOT NULL AUTO_INCREMENT,
  `ronda_id` int DEFAULT NULL,
  `sector_id` int DEFAULT NULL,
  `vigilador_id` int DEFAULT NULL,
  `fecha_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idEscaneo`),
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
  `idLegajo` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `archivo` varchar(255) NOT NULL,
  `fecha_subida` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idLegajo`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

DROP TABLE IF EXISTS `mensajes`;
CREATE TABLE IF NOT EXISTS `mensajes` (
  `idMensaje` int NOT NULL AUTO_INCREMENT,
  `remitente_id` int DEFAULT NULL,
  `destinatario_id` int DEFAULT NULL,
  `contenido` text NOT NULL,
  `fecha_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idMensaje`),
  KEY `remitente_id` (`remitente_id`),
  KEY `destinatario_id` (`destinatario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `novedades`
--

DROP TABLE IF EXISTS `novedades`;
CREATE TABLE IF NOT EXISTS `novedades` (
  `idNovedad` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `objetivo_id` int DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `tipo_registro` varchar(10) DEFAULT NULL,
  `detalle` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idNovedad`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `objetivos`
--

DROP TABLE IF EXISTS `objetivos`;
CREATE TABLE IF NOT EXISTS `objetivos` (
  `idObjetivo` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `localidad` varchar(100) NOT NULL,
  `referente` varchar(100) NOT NULL,
  `tipo` enum('fijo','movil','eventual') NOT NULL,
  PRIMARY KEY (`idObjetivo`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reporte_hombre_vivo`
--

DROP TABLE IF EXISTS `reporte_hombre_vivo`;
CREATE TABLE IF NOT EXISTS `reporte_hombre_vivo` (
  `idReporte` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  PRIMARY KEY (`idReporte`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rondas`
--

DROP TABLE IF EXISTS `rondas`;
CREATE TABLE IF NOT EXISTS `rondas` (
  `idRonda` int NOT NULL AUTO_INCREMENT,
  `puesto` varchar(100) NOT NULL,
  `objetivo_id` int NOT NULL,
  `tipo` enum('Fija','Eventual') NOT NULL,
  `orden_escaneo` int NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idRonda`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;



-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `idUsuario` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `dni` varchar(15) NOT NULL,
  `pass` varchar(150) NOT NULL,
  `f_nac` date NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `tel_emergencia` varchar(20) DEFAULT NULL,
  `nombre_contacto` varchar(30) NOT NULL,
  `parentesco` varchar(30) NOT NULL,
  `domicilio` varchar(100) DEFAULT NULL,
  `provincia` varchar(30) DEFAULT NULL,
  `rol` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `imgPerfil` varchar(60) DEFAULT NULL,
  `imgRepriv` varchar(60) DEFAULT NULL,
  `resetPass` int NOT NULL,
  `activo` int NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idUsuario`),
  UNIQUE KEY `dni` (`dni`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idUsuario`, `nombre`, `apellido`, `dni`, `pass`, `f_nac`, `telefono`, `tel_emergencia`, `nombre_contacto`, `parentesco`, `domicilio`, `provincia`, `rol`, `imgPerfil`, `imgRepriv`, `resetPass`, `activo`, `fecha_creacion`) VALUES

(5, 'Admin', 'Admin', '12456789', '$2y$10$rxO8nd1EIiK7pFH8lh5GR.4rstP2hP5NXB2QXc.VRaijiMQ9eHnau', '2000-03-15', '2613334444', '2615553333', '', '', 'Av Siempre Viva 123', 'Mendoza', 'Administrador', 'img/perfil/AdminAdminPerfil.png', 'img/repriv/AdminAdminRepriv.png', 1, 1, '2025-03-15 21:22:52')

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_objetivo`
--

DROP TABLE IF EXISTS `usuario_objetivo`;
CREATE TABLE IF NOT EXISTS `usuario_objetivo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `objetivo_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `objetivo_id` (`objetivo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `visitas`
--

DROP TABLE IF EXISTS `visitas`;
CREATE TABLE IF NOT EXISTS `visitas` (
  `idVisita` int NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`idVisita`),
  KEY `domicilio_id` (`domicilio_id`),
  KEY `vigilador_id` (`vigilador_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
