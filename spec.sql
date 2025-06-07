-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 06-06-2025 a las 16:24:47
-- Versión del servidor: 8.3.0
-- Versión de PHP: 8.2.18

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
-- Estructura de tabla para la tabla `asignaciones_turnos`
--

DROP TABLE IF EXISTS `asignaciones_turnos`;
CREATE TABLE IF NOT EXISTS `asignaciones_turnos` (
  `idAsignaTurno` int NOT NULL AUTO_INCREMENT,
  `ronda_id` int NOT NULL,
  `vigilador_id` int NOT NULL,
  `fecha` date NOT NULL,
  `turno` enum('Diurno','Nocturno') NOT NULL,
  PRIMARY KEY (`idAsignaTurno`),
  KEY `ronda_id` (`ronda_id`),
  KEY `vigilador_id` (`vigilador_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bajas`
--

DROP TABLE IF EXISTS `bajas`;
CREATE TABLE IF NOT EXISTS `bajas` (
  `idBaja` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `motivo` varchar(255) NOT NULL,
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
  `id_objetivo` int NOT NULL,
  `detalle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `adjunto` varchar(255) NULL,
  PRIMARY KEY (`idDirectiva`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `directivas`
--

INSERT INTO `directivas` (`idDirectiva`, `detalle`, `id_objetivo`) VALUES
(12, 'Nueva directiva desde el Cpanel.\r\n\r\nPellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In ac dolor magna. Ut fringilla lacus nisl, sed suscipit ante fringilla facilisis. Maecenas vel nunc at elit facilisis vestibulum vel sit amet justo.', 6),
(13, 'Nueva directiva para Perrupato:\n1 - Lorem ipsum dolor sit amet, consectetur adipiscing elit\n2- Maecenas ante est, pulvinar eu iaculis ac, facilisis sed elit.\n3- Nunc euismod ac quam non tincidunt. ', 7),
(14, 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In ac dolor magna. Ut fringilla lacus nisl, sed suscipit ante fringilla facilisis. Maecenas vel nunc at elit facilisis vestibulum vel sit amet justo.', 7),
(15, 'Nueva directiva', 9),
(16, 'Quirofano cerrado de 10 a 14hs', 9);

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `mensajes`
--

INSERT INTO `mensajes` (`idMensaje`, `remitente_id`, `destinatario_id`, `contenido`, `fecha_hora`) VALUES
(1, 5, 7, 'Este es un mensaje para el usuario Vera del hospital Perrupato', '2025-05-20 20:30:13'),
(2, 7, 5, 'Mensaje respondido desde el Usuario VERA del Htal Perrupato', '2025-05-20 20:30:50');

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
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`idObjetivo`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `objetivos`
--

INSERT INTO `objetivos` (`idObjetivo`, `nombre`, `localidad`, `referente`, `tipo`) VALUES
(6, 'Objetivo 11', 'Capital', 'Juan Perez', 'fijo'),
(7, 'Hospital Perrupato', 'San Martín', 'Juan Perez', 'fijo'),
(8, 'Hospital Central', 'Capital', 'Juan Perez', 'fijo'),
(9, 'Objetivo de prueba 22-05', 'Santa Rosa', 'Juan Perez', 'fijo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puestos`
--

DROP TABLE IF EXISTS `puestos`;
CREATE TABLE IF NOT EXISTS `puestos` (
  `idPuesto` int NOT NULL AUTO_INCREMENT,
  `puesto` varchar(100) NOT NULL,
  `objetivo_id` int NOT NULL,
  `tipo` enum('Fijo','Eventual') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`idPuesto`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `reporte_hombre_vivo`
--

INSERT INTO `reporte_hombre_vivo` (`idReporte`, `id_usuario`, `fecha`, `hora`) VALUES
(6, 5, '2025-05-05', '14:44:34'),
(7, 5, '2025-05-05', '14:45:07'),
(8, 5, '2025-05-20', '16:46:33'),
(9, 5, '2025-05-22', '14:23:05');

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
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `rondas`
--

INSERT INTO `rondas` (`idRonda`, `puesto`, `objetivo_id`, `tipo`, `orden_escaneo`, `fecha_creacion`) VALUES
(19, '1- Guardia', 7, 'Fija', 1, '2025-05-16 23:48:28'),
(20, '2- Puerta Nte 10hs', 7, 'Fija', 2, '2025-05-16 23:48:28'),
(21, '3- IN PROV', 7, 'Fija', 3, '2025-05-16 23:48:28'),
(22, '4 - MATERNIDAD', 7, 'Fija', 4, '2025-05-16 23:48:28'),
(23, '5-PASILLO Q 8HS', 7, 'Fija', 5, '2025-05-16 23:48:28'),
(24, '6- REF PTA NORTE', 7, 'Fija', 6, '2025-05-16 23:48:28'),
(25, '7- PORTON SUR', 7, 'Fija', 7, '2025-05-16 23:48:28'),
(26, '8-CON EXT 12HS', 7, 'Fija', 8, '2025-05-16 23:48:28'),
(27, '9- PORTON GUARDIA', 7, 'Fija', 9, '2025-05-16 23:48:28'),
(28, '10- COMP VIEJAS 9HS', 7, 'Fija', 10, '2025-05-16 23:48:28'),
(29, '11 - REFERENTE', 7, 'Fija', 11, '2025-05-16 23:48:28'),
(30, '12 - MONITOREO', 7, 'Fija', 12, '2025-05-16 23:48:28'),
(31, 'Puesto 1', 9, 'Fija', 1, '2025-05-22 17:01:48'),
(32, 'Puesto 2', 9, 'Fija', 2, '2025-05-22 17:01:48');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

DROP TABLE IF EXISTS `turnos`;
CREATE TABLE IF NOT EXISTS `turnos` (
  `idTurno` INT NOT NULL AUTO_INCREMENT,
  `objetivo_id` INT DEFAULT NULL,
  `puesto_id` INT DEFAULT NULL,
  `fecha` DATE NOT NULL,
  `turno` ENUM('Diurno','Nocturno') NOT NULL,
  `vigilador_id` INT NOT NULL,
  `tipo_jornada` ENUM('Normal','Guardia Pasiva','Franco','Licencia') NOT NULL,
  `is_referente` TINYINT(1) NOT NULL DEFAULT 0,
  `entrada` TIME DEFAULT NULL,
  `salida` TIME DEFAULT NULL,
  `color` VARCHAR(7) NOT NULL DEFAULT '#FFFFFF',
  PRIMARY KEY (`idTurno`),
  KEY `objetivo_id` (`objetivo_id`),
  KEY `puesto_id` (`puesto_id`),
  KEY `vigilador_id` (`vigilador_id`)
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `turnos`
--

INSERT INTO `turnos` (`idTurno`, `objetivo_id`, `puesto_id`, `fecha`, `turno`, `vigilador_id`, `actividad`, `entrada`, `salida`, `color`) VALUES
(1, 7, 19, '2025-05-19', 'Diurno', 15, 'Franco', '06:00:00', '18:00:00', '#a292f2'),
(2, 7, 19, '2025-05-19', 'Diurno', 7, 'Normal', '06:00:00', '18:00:00', '#ffffff'),
(3, 7, 19, '2025-05-19', 'Nocturno', 17, 'Normal', '18:00:00', '08:00:00', '#ffffff'),
(4, 7, 20, '2025-05-19', 'Diurno', 8, 'Normal', '06:00:00', '18:00:00', '#9ff494'),
(5, 7, 20, '2025-05-20', 'Diurno', 9, 'Normal', '06:00:00', '18:00:00', '#ffb3b3'),
(6, 7, 20, '2025-05-19', 'Nocturno', 8, 'Normal', '18:00:00', '08:00:00', '#ffffff'),
(7, 7, 20, '2025-05-19', 'Guardia Pasiva', 14, 'Guardia Pasiva', '06:00:00', '18:00:00', '#6bf561'),
(8, 7, 20, '2025-05-19', 'Nocturno', 16, 'Referente', '06:00:00', '18:00:00', '#f0f254'),
(9, 7, 21, '2025-05-21', 'Guardia Pasiva', 15, 'Guardia Pasiva', '06:00:00', '18:00:00', '#6bd3e1'),
(10, 7, 24, '2025-05-22', 'Diurno', 12, 'Normal', '06:00:00', '18:00:00', '#ffffff'),
(11, 7, 19, '2025-05-20', 'Diurno', 11, 'Normal', '06:00:00', '18:00:00', '#ffffff'),
(12, 7, 19, '2025-05-20', 'Nocturno', 8, 'Referente', '18:00:00', '08:00:00', '#f5e53d'),
(13, 9, 31, '2025-05-26', 'Diurno', 7, 'Normal', '06:00:00', '18:00:00', '#95d7f4'),
(14, 7, 29, '2025-05-22', 'Diurno', 18, 'Normal', '06:00:00', '12:30:00', '#d4ff00'),
(15, 7, 25, '2025-05-23', 'Nocturno', 18, 'Normal', '20:00:00', '00:00:00', '#ffffff'),
(16, 9, 31, '2025-05-24', 'Nocturno', 18, 'Normal', '21:00:00', '06:00:00', '#ffffff');

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
  `activo` TINYINT(1) NOT NULL DEFAULT 1,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idUsuario`),
  UNIQUE KEY `dni` (`dni`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idUsuario`, `nombre`, `apellido`, `dni`, `pass`, `f_nac`, `telefono`, `tel_emergencia`, `nombre_contacto`, `parentesco`, `domicilio`, `provincia`, `rol`, `imgPerfil`, `imgRepriv`, `resetPass`, `activo`, `fecha_creacion`) VALUES
(5, 'Admin', 'Admin', '12456789', '$2y$10$rxO8nd1EIiK7pFH8lh5GR.4rstP2hP5NXB2QXc.VRaijiMQ9eHnau', '2000-03-15', '2613334444', '2615553333', '', '', 'Av Siempre Viva 123', 'Mendoza', 'Administrador', 'img/perfil/AdminAdminPerfil.png', 'img/repriv/AdminAdminRepriv.png', 1, 1, '2025-03-15 21:22:52'),
(7, 'J', 'VERA', '11111111', '$2y$10$sLSgXzcZcddTV/H2MgghiuslZDGklMOfMvro6CV4aD8akBaje3Ld6', '2000-01-01', '2616666333', '2615553333', '', '', 'Chuquisaca 740', 'Mendoza', 'Vigilador', 'img/perfil/JVERAPerfil.png', 'img/repriv/JVERARepriv.png', 1, 1, '2025-05-16 23:51:05'),
(8, 'J', 'RODRIGUEZ', '22222222', '$2y$10$XSc00qKXmDKNHQq8NjeyF.tehBriNsRV2IUS1h150upVXPh/CYL4q', '2001-01-01', '2634555666', '2615553333', '', '', 'Calle Falsa 123', 'Mendoza', 'Vigilador', 'img/perfil/JRODRIGUEZPerfil.png', 'img/repriv/JRODRIGUEZRepriv.png', 1, 1, '2025-05-16 23:51:59'),
(9, 'M', 'LUCERO', '3333333', '$2y$10$Z8m4wQI3qB40uwAlwFhfOeV2ddvoFLqOalaos8fxL6CLtPtq6Cpdq', '2000-01-02', '2616663333', '2615553333', '', '', 'Entre Rios 158', 'Mendoza', 'Vigilador', 'img/perfil/MLUCEROPerfil.png', 'img/repriv/MLUCERORepriv.png', 1, 1, '2025-05-16 23:53:45'),
(10, 'A', 'QUIROGA', '33322224', '$2y$10$MtnJHg2yVnGDi4A5Dt71W.GdCmuM8uhbgcdlKK9gdw/QjVA2NfnpK', '2000-01-03', '7894513', '2615553333', '', '', 'Chuquisaca 740', 'Mendoza', 'Vigilador', 'img/perfil/AQUIROGAPerfil.png', 'img/repriv/AQUIROGARepriv.png', 1, 1, '2025-05-16 23:55:09'),
(11, 'M', 'SOSA', '44444444', '$2y$10$vT5HkRdTxIubJWzA8biShONC.3q/P3HpRksqL9qLB7okom6nfM4B2', '2004-01-04', '2613334444', '2615553333', '', '', 'Av Siempre Viva 123', 'Mendoza', 'Vigilador', 'img/perfil/MSOSAPerfil.png', 'img/repriv/MSOSARepriv.png', 1, 1, '2025-05-16 23:56:03'),
(12, 'F', 'FERRIGNO', '55555555', '$2y$10$WioP3xh07BSzyBtU5ONMLunzRPdzX2iBhAuK9wO.3nN5p5djx60/2', '2000-01-05', '2616666333', '2615553333', '', '', 'Calle Falsa 123', 'Mendoza', 'Vigilador', 'img/perfil/FFERRIGNOPerfil.png', 'img/repriv/FFERRIGNORepriv.png', 1, 1, '2025-05-16 23:56:38'),
(13, 'N', 'LUCERO', '12355555', '$2y$10$qZdda6tvUrra4yROVCldf.go5DAsllCahwG86lHABbWUkGTEoeEqi', '2000-01-05', '2616663333', '2615553333', '', '', 'Entre Rios 158', 'Mendoza', 'Vigilador', 'img/perfil/NLUCEROPerfil.png', 'img/repriv/NLUCERORepriv.png', 1, 1, '2025-05-16 23:57:25'),
(14, 'L', 'MORALES', '32111111', '$2y$10$nGzHpdxmEhc7VYDgo8Bh4./ihvdL.1FBnP0JYCTYyLHp1vq6/rx1.', '1999-04-04', '2616663333', '2615553333', '', '', 'Entre Rios 158', 'Chubut', 'Vigilador', 'img/perfil/LMORALESPerfil.png', 'img/repriv/LMORALESRepriv.png', 1, 1, '2025-05-16 23:58:52'),
(15, 'C', 'CARRAL', '20111111', '$2y$10$miDzWlNaNzdQqRr4WCpW8uf412GjWIPAZif9Y5T1K5Y5QiaroVkza', '1998-08-05', '2616666333', '2615553333', '', '', 'Calle Falsa 123', 'Córdoba', 'Vigilador', 'img/perfil/CCARRALPerfil.png', 'img/repriv/CCARRALRepriv.png', 1, 1, '2025-05-16 23:59:35'),
(16, 'C', 'CAPADONA', '30222222', '$2y$10$OayoqEwC8WfM5xjJdbDRKew5QSUXXwWdiT7KQSGoR26Ytjjj6Qjpe', '1998-08-05', '2613334444', '2615553333', '', '', 'Av Siempre Viva 123', 'Mendoza', 'Vigilador', 'img/perfil/CCAPADONAPerfil.png', 'img/repriv/CCAPADONARepriv.png', 1, 1, '2025-05-17 00:00:22'),
(17, 'G', 'GODOY', '20555744', '$2y$10$aeVmnoINJ/lre0YFs82yFucTxww194fsg3p/xn84lYxSuN3Oek4pq', '1975-06-05', '2613334444', '2615553333', '', '', 'Av Siempre Viva 123', 'Formosa', 'Vigilador', 'img/perfil/GGODOYPerfil.png', 'img/repriv/GGODOYRepriv.png', 1, 1, '2025-05-17 00:01:08'),
(18, 'Javier', 'Pineda', '31816334', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1985-10-17', '2616666333', '2615553333', '', '', 'Av Siempre Viva 123', 'Mendoza', 'Vigilador', 'img/perfil/JavierPinedaPerfil.png', 'img/repriv/JavierPinedaRepriv.png', 1, 1, '2025-05-22 17:20:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_objetivo`
--

DROP TABLE IF EXISTS `usuario_objetivo`;
CREATE TABLE IF NOT EXISTS `usuario_objetivo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `objetivo_id` int DEFAULT NULL,
  `fecha` DATE NOT NULL,
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
