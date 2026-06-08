-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 08-06-2026 a las 11:58:05
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
-- Estructura de tabla para la tabla `alertas`
--

DROP TABLE IF EXISTS `alertas`;
CREATE TABLE IF NOT EXISTS `alertas` (
  `idAlerta` int NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) DEFAULT NULL,
  `mensaje` text,
  `usuario_id` int DEFAULT NULL,
  `objetivo_id` int DEFAULT NULL,
  `creada_en` datetime DEFAULT CURRENT_TIMESTAMP,
  `leida` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`idAlerta`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `push_subscriptions`
--

DROP TABLE IF EXISTS `push_subscriptions`;
CREATE TABLE IF NOT EXISTS `push_subscriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `endpoint` varchar(191) NOT NULL,
  `p256dh` varchar(255) NOT NULL,
  `auth` varchar(255) NOT NULL,
  `content_encoding` varchar(20) DEFAULT 'aes128gcm',
  `user_agent` varchar(255) DEFAULT NULL,
  `subscription_json` longtext,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_endpoint` (`endpoint`),
  KEY `idx_usuario_id` (`usuario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `art`
--

DROP TABLE IF EXISTS `art`;
CREATE TABLE IF NOT EXISTS `art` (
  `idArt` int NOT NULL AUTO_INCREMENT,
  `razon_social` varchar(255) DEFAULT NULL,
  `cuit_empresa` varchar(11) DEFAULT NULL,
  `telefono_empresa` varchar(20) DEFAULT NULL,
  `empresa_aseguradora` varchar(255) DEFAULT NULL,
  `cuit_aseguradora` varchar(11) DEFAULT NULL,
  `nro_poliza` varchar(100) DEFAULT NULL,
  `telefono_aseguradora` varchar(20) DEFAULT NULL,
  `fecha_alta` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idArt`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bajas`
--

DROP TABLE IF EXISTS `bajas`;
CREATE TABLE IF NOT EXISTS `bajas` (
  `idBaja` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `motivo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `fecha` date NOT NULL,
  `eliminado_por` int NOT NULL,
  PRIMARY KEY (`idBaja`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datos_personales`
--

DROP TABLE IF EXISTS `datos_personales`;
CREATE TABLE IF NOT EXISTS `datos_personales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `estado_civil` varchar(20) DEFAULT NULL,
  `pareja_nombre` varchar(100) DEFAULT NULL,
  `pareja_nacimiento` date DEFAULT NULL,
  `pareja_dni` varchar(20) DEFAULT NULL,
  `nivel_estudio` enum('primario_incompleto','primario_completo','secundario_incompleto','secundario_completo','terciario_incompleto','terciario_completo','universitario_incompleto','universitario_completo') DEFAULT NULL,
  `hijos` json DEFAULT NULL,
  `hijos_adoptivos` json DEFAULT NULL,
  `padres` json DEFAULT NULL,
  `hermanos` json DEFAULT NULL,
  `tutores_discapacidad` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `directivas`
--

DROP TABLE IF EXISTS `directivas`;
CREATE TABLE IF NOT EXISTS `directivas` (
  `idDirectiva` int NOT NULL AUTO_INCREMENT,
  `detalle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `adjunto` varchar(255) DEFAULT NULL,
  `tipo` enum('general','particular','eventual') NOT NULL DEFAULT 'particular',
  `id_objetivo` int NOT NULL,
  PRIMARY KEY (`idDirectiva`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

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
-- Estructura de tabla para la tabla `feriados`
--

DROP TABLE IF EXISTS `feriados`;
CREATE TABLE IF NOT EXISTS `feriados` (
  `idFeriado` int NOT NULL AUTO_INCREMENT,
  `fecha` date NOT NULL,
  `motivo` varchar(70) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `tipo_feriado` varchar(50) NOT NULL,
  PRIMARY KEY (`idFeriado`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `feriados`
--

INSERT INTO `feriados` (`idFeriado`, `fecha`, `motivo`, `tipo_feriado`) VALUES
(1, '2025-10-10', 'Dia de la Diversidad Cultural', 'nacional'),
(2, '2025-11-24', 'Dia de la Memoria', 'nacional');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcaciones_servicio`
--

DROP TABLE IF EXISTS `marcaciones_servicio`;
CREATE TABLE IF NOT EXISTS `marcaciones_servicio` (
  `idMarcacion` int NOT NULL AUTO_INCREMENT,
  `vigilador_id` int NOT NULL,
  `objetivo_id` int DEFAULT NULL,
  `puesto_id` int NOT NULL,
  `tipo_evento` enum('entrada','salida') NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `latitud` decimal(10,8) NOT NULL,
  `longitud` decimal(11,8) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idMarcacion`)
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
  `objetivo_id` int DEFAULT NULL,
  `fecha_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `leido` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`idMensaje`),
  KEY `remitente_id` (`remitente_id`),
  KEY `destinatario_id` (`destinatario_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `mensajes`
--

INSERT INTO `mensajes` (`idMensaje`, `remitente_id`, `destinatario_id`, `contenido`, `objetivo_id`, `fecha_hora`, `leido`) VALUES
(1, 2, 1, 'Nuevo mensajes', NULL, '2025-09-04 15:28:22', 0),
(2, 1, 2, 'Nuevo mensaje del programador', NULL, '2025-09-04 15:54:59', 0),
(3, 2, 1, 'Nuevo mensaje al programador', NULL, '2025-09-04 15:55:55', 0),
(4, 1, 2, 'Nuevo mensaje para gerencia desde programador', NULL, '2025-09-19 13:15:53', 0),
(5, 2, 1, 'Muchas gracias! Recibido conforme', NULL, '2025-09-19 13:49:01', 0),
(6, 2, 1, 'Mensaje de prueba para ver si llega, luego vemos las notificaciones', NULL, '2025-09-20 19:12:30', 0),
(7, 1, 2, 'LLegó y hubo notificación sonora', NULL, '2025-09-20 19:13:36', 0),
(8, 2, 1, 'Otro mensaje nuevo para tyu', NULL, '2025-09-20 19:26:46', 0),
(9, 1, 2, 'gracias! Todo en orden', NULL, '2025-09-20 19:28:51', 0),
(10, 2, 1, 'Tem certeza?', NULL, '2025-09-20 19:29:34', 0),
(11, 2, 1, 'Otro msj para ti', NULL, '2025-09-20 19:34:03', 0),
(12, 2, 1, 'fasdfsaf', NULL, '2025-09-20 19:35:50', 0),
(13, 2, 1, '54654564654fd56g456df4g56d4fg56fd', NULL, '2025-09-20 19:36:10', 0),
(14, 2, 1, 'fgsdfgsdg', NULL, '2025-09-20 19:39:27', 0),
(15, 2, 1, 'fsdfafsd', NULL, '2025-09-20 19:39:37', 0),
(16, 2, 1, 'dfsdaf', NULL, '2025-09-20 19:42:54', 0),
(17, 1, 2, 'ghfgdhfdgh', NULL, '2025-09-25 19:25:19', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `novedades`
--

DROP TABLE IF EXISTS `novedades`;
CREATE TABLE IF NOT EXISTS `novedades` (
  `idNovedad` int NOT NULL AUTO_INCREMENT,
  `vigilador_id` int NOT NULL,
  `objetivo_id` int DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `detalle` text,
  `adjunto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idNovedad`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `objetivos`
--

DROP TABLE IF EXISTS `objetivos`;
CREATE TABLE IF NOT EXISTS `objetivos` (
  `idObjetivo` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `latitud` decimal(10,8) NOT NULL,
  `longitud` decimal(11,8) NOT NULL,
  `radio_m` int NOT NULL DEFAULT '200',
  `localidad` varchar(100) NOT NULL,
  `tipo` enum('fijo','movil','eventual') NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`idObjetivo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `objetivo_siglas`
--

DROP TABLE IF EXISTS `objetivo_siglas`;
CREATE TABLE IF NOT EXISTS `objetivo_siglas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `objetivo_id` int NOT NULL,
  `sigla` varchar(20) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `horas` decimal(5,2) NOT NULL DEFAULT '0.00',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_objetivo_sigla` (`objetivo_id`,`sigla`),
  KEY `objetivo_id` (`objetivo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `objetivo_referentes`
--

DROP TABLE IF EXISTS `objetivo_referentes`;
CREATE TABLE IF NOT EXISTS `objetivo_referentes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `objetivo_id` int NOT NULL,
  `referente_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `objetivo_id` (`objetivo_id`),
  KEY `referente_id` (`referente_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `objetivo_vigiladores`
--

DROP TABLE IF EXISTS `objetivo_vigiladores`;
CREATE TABLE IF NOT EXISTS `objetivo_vigiladores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `objetivo_id` int NOT NULL,
  `vigilador_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `objetivo_id` (`objetivo_id`),
  KEY `vigilador_id` (`vigilador_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `controlador` varchar(100) NOT NULL,
  `accion` varchar(100) NOT NULL,
  `alias` varchar(100) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uix_controlador_accion` (`controlador`,`accion`)
) ENGINE=InnoDB AUTO_INCREMENT=118229 DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `permissions`
--

INSERT INTO `permissions` (`id`, `controlador`, `accion`, `alias`, `descripcion`) VALUES
(1, 'permisos', 'index', 'Otorgar permisos', 'Permite crear permisos. Solo super administradores'),
(2, 'permisos', 'update', 'Actualizar permisos', 'Permite editar permisos. Solo super administradores'),
(3, 'alertas', 'registrarDemoraHombreVivo', 'Registrar demora en Hombre vivo', 'Permite registrar en la BD las demoras en reporte de hombre vivo'),
(5430, 'art', 'vistaCredencialArt', 'Ver Credencial A.R.T', 'Permite ver y descargar una imagen de la credencial de la ART'),
(5527, 'art', 'vistaListadoArt', 'Listado de A.R.T', 'Muestra todas las A.R.T'),
(5656, 'art', 'vistaCrearArt', 'Crear A.R.T (formulario)', 'Permite crear una ART'),
(5817, 'art', 'vistaEditarArt', 'Modificar A.R.T (formulario)', 'Permite mostrar el formulario para modificar los datos'),
(49244, 'cronograma', 'vistaCrearCronograma', 'Crear Cronograma (formulario)', 'Permite crear el cronograma por mes y objetivo'),
(49689, 'cronograma', 'vistaListadoCronogramas', 'Listado de cronogramas (formulario)', 'Muestra el listado de cronogramas con filtros'),
(50171, 'cronograma', 'vistaListadoCronogramaPorVigilador', 'Cronogramas por vigilador (formulario)', 'Muestra el cronograma por vigilador'),
(50718, 'cronograma', 'vistaJornadasPorObjetivo', 'Jornadas por objetivo (formulario)', 'Calcula la cantidad de jornadas trabajadas'),
(51274, 'cronograma', 'vistaHorasPorVigilador', 'Horas por Vigilador (formulario)', 'Calcula las horas trabajadas por vigilador'),
(51867, 'cronograma', 'vistaReporteHorasPorObjetivo', 'Horas por objetivos (formulario)', 'Calcula las horas trabajadas por objetivos'),
(54473, 'directivas', 'crtEliminarDirectiva', 'Eliminar Directiva', 'Permite eliminar directivas que no sean generales'),
(55200, 'directivas', 'vistaListadoDirectivas', 'Mostrar todas las directivas', 'Muestra un listado de todas las directivas creadas'),
(55965, 'directivas', 'vistaCrearDirectiva', 'Crear directiva (formulario)', 'Permite crear distintos tipos de directivas'),
(56136, 'directivas', 'vistaEditarDirectiva', 'Editar Directiva (formulario)', 'Permite modificar directivas'),
(57332, 'feriados', 'ctrEliminarFeriado', 'Eliminar Feriados', 'Permite eliminar los feriados del sistema'),
(57672, 'feriados', 'vistaCrearFeriados', 'Crear Feriado', 'Permite cargar feriados al sistema para calcular mejor los cronogramas'),
(57801, 'feriados', 'vistaListadoFeriados', 'Mostrar todos los feriados', 'Muestra todos los feriados cargados'),
(57962, 'feriados', 'vistaEditarFeriado', 'Editar Feriado', 'Permite modificar un feriado en el sistema'),
(58542, 'hvivo', 'vistaHombreVivo', 'Registrar hombre vivo', 'Permite registrar cada 30 minutos el reporte de hombre vivo'),
(59021, 'hvivo', 'vistaListadoReportesHombreVivo', 'Mostrar todos los reportes de hombre vivo', 'Muestra todos los reportes de hombre vivo con filtros ajustables'),
(61445, 'marcaciones', 'crtRegistrarMarcacion', 'Registrar entrada o salida', 'Permite registrar la entrada o salida del servicio'),
(62148, 'mensajes', 'crtGuardarMensaje', 'Guardar mensajes', 'Guarda los mensajes en la BD'),
(62474, 'mensajes', 'crtMostrarMensajesEnviados', 'Mostrar mensajes enviados', 'Muestra todos los mensajes'),
(62579, 'mensajes', 'crtMostrarUnMensaje', 'Mostrar mensaje', 'Muestra un mensaje en particular'),
(67782, 'noticias', 'vistaCumple', 'Ver cumpleaños del mes', 'Permite ver todos los cumpleaños del mes'),
(68815, 'novedades', 'vistaListadoNovedades', 'Listado de novedades', 'Permite ver un reporte de todas las novedades'),
(69164, 'novedades', 'vistaListadoEntradaSalida', 'Listado de entradas y salidas', 'Permite ver un reporte de entradas y salidas al servicio'),
(71950, 'novedades', 'vistaEntradaSalida', 'Entradas y salidas', 'Permite registrar en el sistema la entrada o salida del servicio'),
(72121, 'novedades', 'vistaCrearNovedades', 'Crear novedades', 'Permite registrar en el sistema alguna novedad'),
(72326, 'novedades', 'vistaHistorialMarcaciones', 'Historial de entradas y salidas', 'Permita ver un historial de entradas y salidas con detalles de objetivo y marcación con GPS'),
(75162, 'objetivos', 'crtDesactivarObjetivo', 'Desactivar objetivos', 'Permite desactivar un objetivo determinado'),
(75778, 'objetivos', 'crtReactivarObjetivo', 'Reactivar objetivos', 'Permite reactivar un objetivo determinado'),
(76591, 'objetivos', 'vistaListadoObjetivos', 'Listado objetivos activos', 'Muestra un listado de objetivos activos'),
(76917, 'objetivos', 'vistaListadoObjetivosInactivos', 'Listado objetivos inactivos', 'Muestra un listado de objetivos inactivos'),
(77308, 'objetivos', 'vistaCrearObjetivo', 'Crear objetivo(formulario)', 'Permite crear objetivos'),
(77568, 'objetivos', 'vistaEditarObjetivo', 'Modificar objetivo (formulario)', 'Permite editar los datos de un objetivo'),
(80031, 'puestos', 'crtDesactivarPuesto', 'Desactivar puestos', 'Permite desactivar puestos'),
(80647, 'puestos', 'crtReactivarPuesto', 'Reactivar puestos', 'Permite reactivar puestos'),
(81436, 'puestos', 'vistaListadoPuestos', 'Listado de puestos activos', 'Muestra un reporte de puestos activos'),
(81762, 'puestos', 'vistaListadoPuestosDesactivados', 'Listado de puestos inactivos', 'Muestra un reporte de puestos inactivos'),
(82153, 'puestos', 'vistaCrearPuestos', 'Crear puestos', 'Permite crear puestos en los objetivos'),
(82413, 'puestos', 'vistaEditarPuesto', 'Modificar puesto', 'Permite editar los datos de un puesto en particular'),
(82726, 'puestos', 'vistaRotaciones', 'Crear rotaciones de puestos', 'Permite crear rotaciones entre los puestos'),
(86187, 'puestos', 'crtEliminarRotacion', 'Eliminar rotaciones', 'Permite reescribir las rotaciones almacenadas en la BD'),
(87200, 'puestos', 'crtSwapRotacion', 'Intercambiar puestos con vigiladores', 'Permite intercambiar puestos entre vigiladores entre pares (uno por otro)'),
(89505, 'puestos', 'crtAutoRotarEquitativo', 'Auto rotación de puestos', 'Permite crear una rotación de puestos entre vigiladores'),
(92329, 'roles', 'vistaListadoRoles', 'Listado de roles', 'Muestra un listado de roles creados'),
(92372, 'roles', 'vistaCrearRol', 'Crear rol (formulario)', 'Permite crear un nuevo'),
(93466, 'roles', 'vistaEditarRol', 'Modificar rol (formulario)', 'Permite modificar los datos de un rol en particular'),
(95771, 'roles', 'ctrDesactivarRol', 'Desactivar roles', 'Permite desactivar roles'),
(96348, 'roles', 'vistaPermisosRol', 'Gestionar roles (formulario)', 'Permite gestionar roles y asignar los permisos'),
(99128, 'roles', 'ctrGuardarPermisosRol', 'Guardar permisos de roles', 'Guarda los permisos otorgados en la BD'),
(102343, 'rondas', 'crtDesactivarRonda', 'Desactivar rondas', NULL),
(105324, 'rondas', 'vistaListadoRondas', 'Listado de rondas', 'Muestra un listado de rondas por objetivo'),
(105780, 'rondas', 'vistaCrearRondas', 'Crear rondas', 'Permite crear rondas'),
(106077, 'rondas', 'vistaEditarRondas', 'Modificar ronda ', 'Permite editar y actualizar los datos de una ronda'),
(106411, 'rondas', 'vistaEscanearRondas', 'Escanear rondas', 'Permite escanear los códigos QR de las rondas'),
(110645, 'salud', 'vistaMiSalud', 'Mi salud (formulario)', 'Permite acceder a los datos de mi salud'),
(110676, 'salud', 'guardarSalud', 'Mi salud (ejecución)', 'Permite guardar los datos de mi salud'),
(111909, 'turnos', 'crtBuscarTurnosPorRango', 'Listado de cronogramas (ejecución)', 'Muestra un listado de cronogramas'),
(112202, 'turnos', 'crtBuscarPorVigilador', 'Turnos asignados por vigilador', 'Permite ver los turnos asignados a un vigilador'),
(113721, 'uniformes', 'vistaMiUniforme', 'Mi uniforme', 'Permite ver los datos de mi uniforme'),
(114109, 'uniformes', 'vistaListadoUniformes', 'Listado de uniformes', 'Muestra un informe detallado de uniformes del personal'),
(114224, 'usuarios', 'crtGuardarUsuario', 'Guardar usuario (ejecución)', 'Permite guardar un usuario'),
(114939, 'usuarios', 'crtModificarUsuario', 'Perfil usuario (ejecución)', 'Permite guardar los datos que se actualizan en un perfil determinado'),
(116478, 'usuarios', 'crtReactivarUsuario', 'Reactivar usuario', 'Permite reactivar un usuario dado de baja'),
(117088, 'usuarios', 'vistaListadoUsuarios', 'Listado de usuarios activos', 'Muestra un listado de usuarios activos'),
(117441, 'usuarios', 'vistaListadoUsuariosInactivos', 'Listado de usuarios inactivos', 'Muestra un listado de usuarios inactivos'),
(117767, 'usuarios', 'vistaCrearUsuario', 'Crear usuario (formulario)', 'Permite guardar un usuario'),
(117990, 'usuarios', 'vistaPerfilUsuario', 'Perfil usuario (formulario)', 'Permite acceder a los datos de un perfil en particular');

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
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`idPuesto`)
) ENGINE=MyISAM AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puestos_turnos`
--

DROP TABLE IF EXISTS `puestos_turnos`;
CREATE TABLE IF NOT EXISTS `puestos_turnos` (
  `idPuestoTurno` int NOT NULL AUTO_INCREMENT,
  `puesto_id` int NOT NULL,
  `numero_turno` tinyint NOT NULL,
  `hora_entrada` time NOT NULL,
  `hora_salida` time NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idPuestoTurno`),
  KEY `idx_puesto_turno` (`puesto_id`,`numero_turno`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `hvivo_config`
--

DROP TABLE IF EXISTS `hvivo_config`;
CREATE TABLE IF NOT EXISTS `hvivo_config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `turno` enum('diurno','nocturno') NOT NULL,
  `minutos` int NOT NULL DEFAULT '30',
  `tolerancia_minutos` int NOT NULL DEFAULT '3',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_hvivo_turno` (`turno`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `hvivo_config` (`turno`, `minutos`, `tolerancia_minutos`) VALUES
('diurno', 30, 3),
('nocturno', 30, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reporte_hombre_vivo`
--

DROP TABLE IF EXISTS `reporte_hombre_vivo`;
CREATE TABLE IF NOT EXISTS `reporte_hombre_vivo` (
  `idReporte` int NOT NULL AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `objetivo_id` int NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `demora` time DEFAULT NULL,
  PRIMARY KEY (`idReporte`),
  KEY `idx_usuario` (`id_usuario`),
  KEY `idx_objetivo` (`objetivo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `alias` varchar(100) DEFAULT NULL,
  `nivel` decimal(3,1) NOT NULL DEFAULT '1.0',
  `categoria` enum('operativo','referente','supervisor','administrativo','direccion','reservado') NOT NULL DEFAULT 'operativo',
  `tipo` enum('fijo','temporal') NOT NULL DEFAULT 'fijo',
  `reservado` tinyint(1) NOT NULL DEFAULT '0',
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `alias`, `nivel`, `categoria`, `tipo`, `reservado`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Programador', 'Programador (superusuario)', 99.0, 'reservado', 'fijo', 1, 1, '2025-08-19 02:28:51', '2025-08-31 19:24:58'),
(2, 'Gerencia', 'Gerencia', 5.0, 'direccion', 'fijo', 0, 1, '2025-09-01 12:50:33', '2025-09-01 12:50:33'),
(3, 'Abel', NULL, 4.0, 'administrativo', 'temporal', 0, 0, '2025-09-04 15:00:26', '2025-09-04 15:00:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id` int NOT NULL,
  `permission_id` int NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `fk_rp_permission` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(2, 1),
(2, 2),
(2, 3),
(2, 5430),
(2, 5527),
(2, 5656),
(2, 5817),
(2, 49244),
(2, 49689),
(2, 50171),
(2, 50718),
(2, 51274),
(2, 51867),
(2, 54473),
(2, 55200),
(2, 55965),
(2, 56136),
(2, 57332),
(2, 57672),
(2, 57801),
(2, 57962),
(2, 58542),
(2, 59021),
(2, 61445),
(2, 62148),
(2, 62474),
(2, 62579),
(2, 67782),
(2, 68815),
(2, 69164),
(2, 71950),
(2, 72121),
(2, 72326),
(2, 75162),
(2, 75778),
(2, 76591),
(2, 76917),
(2, 77308),
(2, 77568),
(2, 80031),
(2, 80647),
(2, 81436),
(2, 81762),
(2, 82153),
(2, 82413),
(2, 82726),
(2, 86187),
(2, 87200),
(2, 89505),
(2, 92329),
(2, 92372),
(2, 93466),
(2, 95771),
(2, 96348),
(2, 99128),
(2, 102343),
(2, 105324),
(2, 105780),
(2, 106077),
(2, 106411),
(2, 110645),
(2, 110676),
(2, 111909),
(2, 112202),
(2, 113721),
(2, 114109),
(2, 114224),
(2, 114939),
(2, 116478),
(2, 117088),
(2, 117441),
(2, 117767),
(2, 117990);

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
  `status` enum('draft','active','inactive') NOT NULL DEFAULT 'draft',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idRonda`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rotaciones_log`
--

DROP TABLE IF EXISTS `rotaciones_log`;
CREATE TABLE IF NOT EXISTS `rotaciones_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rotacion_id` int DEFAULT NULL,
  `objetivo_id` int NOT NULL,
  `fecha` date NOT NULL,
  `puesto_id` int NOT NULL,
  `usuario_anterior` int DEFAULT NULL,
  `usuario_nuevo` int DEFAULT NULL,
  `codigo_turno` enum('D','N') NOT NULL,
  `usuario_editor` int NOT NULL,
  `accion` enum('create','update','delete','swap','autofill') NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `fecha_hora` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rotaciones_puestos`
--

DROP TABLE IF EXISTS `rotaciones_puestos`;
CREATE TABLE IF NOT EXISTS `rotaciones_puestos` (
  `idRotacion` int NOT NULL AUTO_INCREMENT,
  `objetivo_id` int NOT NULL,
  `fecha` date NOT NULL,
  `puesto_id` int NOT NULL,
  `usuario_id` int NOT NULL,
  `codigo_turno` enum('D','N') NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idRotacion`),
  UNIQUE KEY `uq_puesto_fecha_turno` (`fecha`,`puesto_id`,`codigo_turno`),
  UNIQUE KEY `uq_usuario_fecha_turno` (`fecha`,`usuario_id`,`codigo_turno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `salud`
--

DROP TABLE IF EXISTS `salud`;
CREATE TABLE IF NOT EXISTS `salud` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `enfermedad_cronica` text,
  `medicacion` text,
  `grupo_sanguineo` varchar(10) DEFAULT NULL,
  `tiene_obra_social` tinyint(1) DEFAULT NULL,
  `obra_social_nombre` varchar(100) DEFAULT NULL,
  `beneficiario` varchar(100) DEFAULT NULL,
  `nro_afiliado` varchar(50) DEFAULT NULL,
  `vigencia_obra_social` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

DROP TABLE IF EXISTS `turnos`;
CREATE TABLE IF NOT EXISTS `turnos` (
  `idTurno` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `objetivo_id` int NOT NULL,
  `fecha` date NOT NULL,
  `rol` enum('Vigilador','Referente') NOT NULL,
  `tipo_turno` enum('Normal','Licencia') NOT NULL DEFAULT 'Normal',
  `codigo_turno` varchar(5) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idTurno`),
  UNIQUE KEY `idx_usuario_fecha` (`usuario_id`,`fecha`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `uniformes`
--

DROP TABLE IF EXISTS `uniformes`;
CREATE TABLE IF NOT EXISTS `uniformes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `talle_pantalon` varchar(5) DEFAULT NULL,
  `talle_remera` varchar(5) DEFAULT NULL,
  `talle_polar` varchar(5) DEFAULT NULL,
  `talle_campera` varchar(5) DEFAULT NULL,
  `talle_calzado` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `uniformes`
--

INSERT INTO `uniformes` (`id`, `usuario_id`, `talle_pantalon`, `talle_remera`, `talle_polar`, `talle_campera`, `talle_calzado`) VALUES
(1, 2, '40', 'XXL', '4XL', '3XL', '40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `idUsuario` int NOT NULL AUTO_INCREMENT,
  `rol_id` int NOT NULL,
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
  `imgPerfil` varchar(60) DEFAULT NULL,
  `imgRepriv` varchar(60) DEFAULT NULL,
  `resetPass` int NOT NULL,
  `activo` int NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idUsuario`),
  UNIQUE KEY `dni` (`dni`),
  KEY `fk_usuarios_roles` (`rol_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idUsuario`, `rol_id`, `nombre`, `apellido`, `dni`, `pass`, `f_nac`, `telefono`, `tel_emergencia`, `nombre_contacto`, `parentesco`, `domicilio`, `provincia`, `imgPerfil`, `imgRepriv`, `resetPass`, `activo`, `fecha_creacion`) VALUES
(1, 1, 'Programador', 'Argus', '31816334', '$2y$10$8JWq7tOTYXu33r5G0H0lEuemfso4tY6sYFn5ShcpUoRzOUhSXpPGK', '1985-10-17', '2616524585', '2617135478', 'Whatsapp', 'Whatsapp', 'Los Pimientos 856, Las Heras', 'Mendoza', NULL, NULL, 1, 1, '2025-09-01 06:44:54'),
(2, 2, 'Gerencia', 'Gerencia', '12456789', '$2y$10$zzElpYlyG8AiK6zTGCSiz.3WqkcfGSm7X3wxfOc52y6Z8MU5Aplha', '2000-01-01', '2613334444', '2615553333', 'Comercial', 'Primo', 'Av Siempre Viva 123', 'Mendoza', NULL, NULL, 1, 1, '2025-09-01 12:51:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_art`
--

DROP TABLE IF EXISTS `usuario_art`;
CREATE TABLE IF NOT EXISTS `usuario_art` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `art_id` int NOT NULL,
  `fecha_asignacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `art_id` (`art_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_objetivo`
--

DROP TABLE IF EXISTS `usuario_objetivo`;
CREATE TABLE IF NOT EXISTS `usuario_objetivo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `objetivo_id` int DEFAULT NULL,
  `fecha` date NOT NULL,
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

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
