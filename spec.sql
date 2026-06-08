-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 25-07-2025 a las 09:06:43
-- Versión del servidor: 8.3.0
-- Versión de PHP: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "-03:00";


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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4  ;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4  ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bajas`
--

DROP TABLE IF EXISTS `bajas`;
CREATE TABLE IF NOT EXISTS `bajas` (
  `idBaja` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `motivo` varchar(255) CHARACTER SET utf8mb4    NOT NULL,
  `fecha` date NOT NULL,
  `eliminado_por` int NOT NULL,
  PRIMARY KEY (`idBaja`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4  ;

--
-- Volcado de datos para la tabla `bajas`
--

INSERT INTO `bajas` (`idBaja`, `usuario_id`, `motivo`, `fecha`, `eliminado_por`) VALUES
(1, 50, 'Se ausentó del servicio en reiteradas ocaciones', '2025-06-07', 5),
(2, 22, 'Se ausentó del servicio en reiteradas ocaciones', '2025-06-07', 5),
(3, 18, 'Se ausentó del servicio en reiteradas ocaciones', '2025-06-09', 5);

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
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4  ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `directivas`
--

DROP TABLE IF EXISTS `directivas`;
CREATE TABLE IF NOT EXISTS `directivas` (
  `idDirectiva` int NOT NULL AUTO_INCREMENT,
  `detalle` text CHARACTER SET utf8mb4    NOT NULL,
  `adjunto` varchar(255) DEFAULT NULL,
  `id_objetivo` int NOT NULL,
  PRIMARY KEY (`idDirectiva`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4  ;

--
-- Volcado de datos para la tabla `directivas`
--

INSERT INTO `directivas` (`idDirectiva`, `detalle`, `adjunto`, `id_objetivo`) VALUES
(27, 'Directiva con adjunto', NULL, 14),
(13, 'Nueva directiva para Perrupato:\n1 - Lorem ipsum dolor sit amet, consectetur adipiscing elit\n2- Maecenas ante est, pulvinar eu iaculis ac, facilisis sed elit.\n3- Nunc euismod ac quam non tincidunt. ', NULL, 7),
(14, 'Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In ac dolor magna. Ut fringilla lacus nisl, sed suscipit ante fringilla facilisis. Maecenas vel nunc at elit facilisis vestibulum vel sit amet justo.', NULL, 7),
(15, 'Nueva directiva', NULL, 9),
(16, 'Quirofano cerrado de 10 a 14hs', NULL, 9),
(25, 'Directiva Nueva para el central EDITADA con adjunto', 'img/directivas/directiva_20250609204529_20250609204529.png', 8),
(18, 'Nueva directiva para el Hospita Notti', NULL, 12),
(19, 'Nueva directiva para el Hospita Notti', NULL, 12),
(20, 'Otra nueva directiva que ha sido modificada', NULL, 12),
(22, 'Otra nueva directiva', NULL, 13),
(23, 'Directiva con adjunto.\r\nNuevo archivo', 'img/directivas/directiva_68437b2c0f094_20250606203508.png', 12),
(24, 'Nuevo objetivo para el Perrupato 09-06-25', NULL, 7);

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4  ;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4  ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marcaciones_servicio`
--

DROP TABLE IF EXISTS `marcaciones_servicio`;
CREATE TABLE IF NOT EXISTS `marcaciones_servicio` (
  `idMarcacion` int NOT NULL AUTO_INCREMENT,
  `vigilador_id` int NOT NULL,
  `objetivo_id` int DEFAULT NULL,
  `tipo_evento` enum('entrada','salida') NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `latitud` decimal(10,8) NOT NULL,
  `longitud` decimal(11,8) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idMarcacion`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4  ;

--
-- Volcado de datos para la tabla `marcaciones_servicio`
--

INSERT INTO `marcaciones_servicio` (`idMarcacion`, `vigilador_id`, `objetivo_id`, `tipo_evento`, `fecha_hora`, `latitud`, `longitud`, `created_at`) VALUES
(1, 5, NULL, 'entrada', '2025-06-07 13:34:00', -32.87169020, -68.84664270, '2025-06-07 16:34:00'),
(2, 5, NULL, 'salida', '2025-06-07 13:34:42', -32.87169020, -68.84664270, '2025-06-07 16:34:42'),
(3, 50, 7, 'entrada', '2025-06-06 06:34:00', -32.87169020, -68.84664270, '2025-06-07 19:34:00'),
(4, 50, 7, 'salida', '2025-06-06 13:34:42', -32.87169020, -68.84664270, '2025-06-07 19:34:42'),
(5, 50, 13, 'entrada', '2025-06-01 18:03:00', -32.87169020, -68.84664270, '2025-06-07 19:34:00'),
(6, 50, 13, 'salida', '2025-06-01 05:34:42', -32.87169020, -68.84664270, '2025-06-07 19:34:42'),
(7, 27, 14, 'entrada', '2025-06-02 06:34:00', -32.87169020, -68.84664270, '2025-06-07 19:34:00'),
(8, 27, 14, 'salida', '2025-06-02 13:34:42', -32.87169020, -68.84664270, '2025-06-07 19:34:42'),
(9, 5, NULL, 'entrada', '2025-06-12 17:10:55', -32.87169020, -68.84664270, '2025-06-12 20:10:55'),
(10, 5, NULL, 'salida', '2025-06-12 17:12:17', -32.87169020, -68.84664270, '2025-06-12 20:12:17');

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4  ;

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
  `vigilador_id` int NOT NULL,
  `objetivo_id` int DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `hora` time DEFAULT NULL,
  `detalle` text,
  `adjunto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idNovedad`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4  ;

--
-- Volcado de datos para la tabla `novedades`
--

INSERT INTO `novedades` (`idNovedad`, `vigilador_id`, `objetivo_id`, `fecha`, `hora`, `detalle`, `adjunto`, `created_at`) VALUES
(1, 5, 8, '2025-06-07', '17:16:00', 'Novedad sin adjunto', NULL, '2025-06-07 20:16:23'),
(2, 5, 12, '2025-06-07', '17:17:00', 'Novedad con adjunto', 'img/novedades/novedad_5_1749327422_20250607171702.png', '2025-06-07 20:17:02'),
(3, 5, 12, '2025-06-09', '22:10:00', 'Nueva novedad para el NOTTI', 'img/novedades/novedad_1749517806_20250609221006.png', '2025-06-10 01:10:06');

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
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4  ;

--
-- Volcado de datos para la tabla `objetivos`
--

INSERT INTO `objetivos` (`idObjetivo`, `nombre`, `latitud`, `longitud`, `radio_m`, `localidad`, `referente`, `tipo`, `activo`) VALUES
(6, 'Objetivo 11 Editado', 0.00000000, 0.00000000, 200, 'Capital', 'Juan Perez', 'fijo', 1),
(7, 'Hospital Perrupato', 0.00000000, 0.00000000, 200, 'San Martín', 'Juan Perez', 'fijo', 1),
(8, 'Hospital Central Editao', -32.89197500, -68.83283300, 300, 'Capital', 'Juan Perez', 'fijo', 1),
(12, 'Hospital Notti', 0.00000000, 0.00000000, 200, 'Guaymallén', NULL, 'fijo', 1),
(13, 'Hospital Saporiti', -33.19693700, -68.46086800, 200, 'San Martín', NULL, 'fijo', 1),
(14, 'Panaderia', -32.86502500, -68.83808800, 100, 'Capital', NULL, 'fijo', 1),
(15, 'Parque Central', -32.87594700, -68.84152300, 200, 'Capital', NULL, 'fijo', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permissions`
--

DROP TABLE IF EXISTS `permissions`;
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `controlador` varchar(100) NOT NULL,
  `accion` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uix_controlador_accion` (`controlador`,`accion`)
) ENGINE=InnoDB AUTO_INCREMENT=349488 DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `permissions`
--

INSERT INTO `permissions` (`id`, `controlador`, `accion`, `descripcion`) VALUES
(65, 'escaneos', 'registrar', 'Registra los QR de las rondas'),
(66, 'login', 'mostrarLogin', 'Procesa el login'),
(67, 'login', 'procesarLogin', 'Procesa el login'),
(68, 'marcaciones', 'crtRegistrarMarcacion', 'Para roles Vigilador y Referente, obtiene el punto central y radio de la zona, calcula la distancia con Haversine y bloquea el registro si está fuera del área.'),
(69, 'novedades', 'crtRegistrar', 'Registra una novedad asociada a un usuario y objetivo'),
(70, 'novedades', 'crtListarNovedades', 'Muestra todas las novedades'),
(71, 'permisos', 'index', 'Permite crear permisos. Solo super administradores'),
(72, 'permisos', 'update', 'Permite editar permisos. Solo super administradores'),
(73, 'plantilla', 'crtGetPlantilla', 'Obtiene la plantilla base de las vistas'),
(74, 'plantilla', 'crtGetLogin', 'Obtiene la vista para login'),
(75, 'qr', 'generar', 'Genera el QR para las rondas'),
(76, 'qr', 'mostrar', 'Muestra en pantalla los QR de las rondas'),
(77, 'qr', 'delete', 'Elimina los QR de las rondas'),
(78, 'rondas', 'crtGuardarRondas', 'Guarda las rondas en la BD'),
(79, 'rondas', 'eliminarImagenesQR', 'Elimina los QR las rondas del servidor'),
(80, 'rondas', 'eliminarErroresQR', 'Elimina el log de errores del servidor'),
(81, 'rondas', 'limpiarSesionQR', 'Borra los datos de Session[\'qr_codes\']'),
(82, 'rondas', 'crtDesactivarRonda', 'Da de baja una ronda'),
(83, 'rondas', 'crtActualizarRonda', 'Actualiza una ronda'),
(84, 'rondas', 'crtListarRondas', 'Muestra las rondas activas'),
(85, 'rutas', 'cargarVista', 'Carga las vistas del proyecto'),
(107, 'archivos', 'guardarArchivo', 'Guarda archivos en la BD y el servidor'),
(1112, 'cronograma', 'crtBuscarResumenDiario', ' Buscar por resumen diario de jornadas trabajadas'),
(3286, 'cronograma', 'calcularHorasEnVentana', 'Reporte de horas trabajadas por objetivos'),
(4372, 'cronograma', 'crtBuscarResumenHorasPorVigilador', 'Reporte de horas trabajadas por vigilador'),
(7715, 'directivas', 'crtGuardarDirectiva', 'Crea una nueva directiva'),
(8101, 'directivas', 'crtModificarDirectiva', 'Modifica una directiva'),
(8994, 'directivas', 'crtEliminarDirectiva', 'Elimina una directiva'),
(9619, 'directivas', 'crtListarDirectivas', 'Muestra todas las directivas'),
(9900, 'directivas', 'vistaCrearDirectiva', 'Muestra la vista para crear directivas'),
(10176, 'directivas', 'vistaEditarDirectiva', 'Muestra la vista para editar directivas'),
(12244, 'mensajes', 'crtMostrarMensajes', 'Muestra todos los mensajes '),
(12291, 'mensajes', 'crtMostrarMensajesEnviados', 'Muestra todos los mensajes enviados'),
(12372, 'mensajes', 'crtMostrarUnMensaje', 'Muestra un mensaje determinado'),
(12475, 'mensajes', 'crtGuardarMensaje', 'Guarda en la BD un mensaje'),
(13264, 'objetivos', 'crtGuardarObjetivo', 'Guarda en la BD un nuevo objetivo'),
(13410, 'objetivos', 'crtModificarObjetivo', 'Modifica en la BD un objetivo'),
(13833, 'objetivos', 'crtDesactivarObjetivo', 'Da de baja un objetivo'),
(14434, 'objetivos', 'crtReactivarObjetivo', 'Da de alta un objetivo desactivado'),
(15227, 'objetivos', 'crtListarObjetivos', 'Muestra los objetivos activos'),
(15727, 'puestos', 'ctrGuardarPuesto', 'Guarda un nuevo puesto '),
(15977, 'puestos', 'crtModificarPuesto', 'Modifica datos de un puesto'),
(16542, 'puestos', 'crtDesactivarPuesto', 'Da de baja un puesto'),
(17143, 'puestos', 'crtReactivarPuesto', 'Da de alta un puesto desactivado'),
(17936, 'puestos', 'crtListarPuestos', 'Muestra todos los puestos activos'),
(24198, 'turnos', 'ctrRegistrarPlanilla', 'Guarda en turnos la planilla de trabajores (cronograma)'),
(24583, 'turnos', 'crtBuscarTurnosPorRango', 'Busca los turnos por rango de fechas'),
(24868, 'turnos', 'crtBuscarPorVigilador', 'Busca los turnos por vigilador'),
(25325, 'usuarios', 'crtGuardarUsuario', 'Guarda un nuevo usuario en la BD'),
(26031, 'usuarios', 'crtModificarUsuario', 'Modifica los datos en la BD'),
(27588, 'usuarios', 'crtReactivarUsuario', 'Da de alta un usuario inactivo'),
(28183, 'usuarios', 'crtListarUsuarios', 'Muestra todos los usuarios activos'),
(34542, 'directivas', 'vistaListadoDirectivas', 'Muestra el listado de las directivas'),
(39979, 'objetivos', 'crtListarObjetivosInactivos', 'Listado de objetivos inactivos'),
(42764, 'puestos', 'crtListarPuestosDesactivados', 'Listado de puestos desactivados'),
(63591, 'novedades', 'vistaListadoNovedades', 'Vista del listado de novedades'),
(63726, 'novedades', 'vistaListadoEntradaSalida', 'Vista del listado de registro de entradas y salidas'),
(63928, 'novedades', 'vistaCrearNovedades', 'Muestra el formulario para crear las novedades'),
(66152, 'objetivos', 'vistaListadoObjetivos', 'Vista del listado de objetivos activos'),
(66478, 'objetivos', 'vistaListadoObjetivosInactivos', 'Vista Listado de objetivos inactivos'),
(69652, 'puestos', 'vistaListadoPuestos', 'Vista del listado de puestos activos'),
(69978, 'puestos', 'vistaListadoPuestosDesactivados', 'Vista del listado de puestos inactivos'),
(75894, 'rondas', 'vistaListadoRondas', 'Vista del listado de rondas activas'),
(81876, 'usuarios', 'vistaListadoUsuarios', 'Vista del listado de usuarios'),
(82137, 'usuarios', 'vistaListadoUsuariosInactivos', 'Vista del listado de usuarios dados de bajas'),
(111722, 'cronograma', 'crtBuscarResumenHoras', 'Reporte horas por objetivo'),
(116083, 'cronograma', 'vistaCrearCronograma', 'Vista para crear cronogramas'),
(116269, 'cronograma', 'vistaListadoCronogramas', 'Vista del listado de cronogramas'),
(116492, 'cronograma', 'vistaListadoCronogramaPorVigilador', 'Reporte de cronograma por Vigilador'),
(116752, 'cronograma', 'vistaJornadasPorObjetivo', 'Reporte de joranadas trabajadas por objetivos'),
(117049, 'cronograma', 'vistaHorasPorVigilador', 'Reportes de horas trabajadas por vigilador'),
(126874, 'objetivos', 'vistaCrearObjetivo', 'Vista del formulario para crear objetivos'),
(127134, 'objetivos', 'vistaEditarObjetivo', 'Vista del formulario para editar objetivos'),
(130925, 'puestos', 'vistaCrearPuestos', 'Vista del formulario para crear puestos'),
(131185, 'puestos', 'vistaEditarPuesto', NULL),
(137457, 'rondas', 'vistaCrearRondas', 'Vista del formulario para crear rondas'),
(137754, 'rondas', 'vistaEditarRondas', 'Vista del formulario para editar rondas'),
(144768, 'usuarios', 'vistaCrearUsuario', 'Muestra el formulario para crear usuarios'),
(144991, 'usuarios', 'vistaPerfilUsuario', 'Vista del Perfil del usuario'),
(205097, 'rondas', 'vistaEscanearRondas', 'Vista para escanear rondas'),
(246464, 'hvivo', 'vistaHombreVivo', 'Ver formulario reporte Hombre Vivo'),
(246465, 'hvivo', 'registrar', 'Registrar reporte Hombre Vivo'),
(289653, 'hvivo', 'vistaListadoReportesHombreVivo', NULL),
(327535, 'novedades', 'vistaEntradaSalida', 'Vista del formulario de entradas y salidas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puestos`
--

DROP TABLE IF EXISTS `puestos`;
CREATE TABLE IF NOT EXISTS `puestos` (
  `idPuesto` int NOT NULL AUTO_INCREMENT,
  `puesto` varchar(100) NOT NULL,
  `objetivo_id` int NOT NULL,
  `tipo` enum('Fijo','Eventual') CHARACTER SET utf8mb4    NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`idPuesto`)
) ENGINE=MyISAM AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4  ;

--
-- Volcado de datos para la tabla `puestos`
--

INSERT INTO `puestos` (`idPuesto`, `puesto`, `objetivo_id`, `tipo`, `activo`) VALUES
(1, '1- Guardia MODIFICADA', 7, 'Fijo', 1),
(2, '2- Puerta Nte 10hs', 7, 'Eventual', 1),
(3, '3- IN PROV', 7, 'Fijo', 1),
(4, '4 - MATERNIDAD', 7, 'Fijo', 1),
(5, '5-PASILLO Q 8HS', 7, 'Fijo', 1),
(6, '6- REF PTA NORTE', 7, 'Fijo', 1),
(7, '7- PORTON SUR', 7, 'Fijo', 1),
(8, '8-CON EXT 12HS', 7, 'Fijo', 1),
(9, '9- PORTON GUARDIA', 7, 'Fijo', 1),
(10, '10- COMP VIEJAS 9HS', 7, 'Fijo', 1),
(11, '11 - REFERENTE', 7, 'Fijo', 1),
(12, '12 - MONITOREO', 7, 'Fijo', 1),
(13, 'Puesto 1', 9, 'Fijo', 1),
(14, 'Puesto 2', 9, 'Fijo', 1),
(15, 'Puesto 3', 9, 'Eventual', 1),
(16, 'Puesto 4', 9, 'Eventual', 1),
(17, 'Puesto 1 Notti', 12, 'Fijo', 1),
(18, 'Puesto 2 Notti', 12, 'Fijo', 1),
(19, 'Puesto 3 Notti', 12, 'Fijo', 1),
(20, 'Puesto 4 Notti', 12, 'Fijo', 1),
(21, 'Puesto 5 Notti', 12, 'Fijo', 1),
(22, 'Puesto 6 Notti', 12, 'Fijo', 1),
(23, 'Puesto 8 Notti', 12, 'Fijo', 1),
(24, 'Puesto 9 Notti', 12, 'Fijo', 1),
(25, 'Puesto 10 Notti', 12, 'Fijo', 1),
(26, 'Puesto 3 Notti', 12, 'Fijo', 1),
(27, 'Reloj del SOL', 15, 'Fijo', 1),
(28, 'Recepcion Panaderia', 14, 'Fijo', 1);

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
  `ronda_id` int NOT NULL,
  `fecha_hora` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `demora` time DEFAULT NULL,
  PRIMARY KEY (`idReporte`),
  KEY `idx_usuario` (`id_usuario`),
  KEY `idx_ronda` (`ronda_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4  ;

--
-- Volcado de datos para la tabla `reporte_hombre_vivo`
--

INSERT INTO `reporte_hombre_vivo` (`idReporte`, `id_usuario`, `ronda_id`, `fecha_hora`, `demora`) VALUES
(1, 5, 12, '2025-06-08 20:41:02', '-29:57:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `role_permissions`
--

DROP TABLE IF EXISTS `role_permissions`;
CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role` varchar(50) NOT NULL,
  `permission_id` int NOT NULL,
  PRIMARY KEY (`role`,`permission_id`),
  KEY `fk_rp_permission` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Volcado de datos para la tabla `role_permissions`
--

INSERT INTO `role_permissions` (`role`, `permission_id`) VALUES
('Gerencia', 65),
('Vigilador', 65),
('Administrativo', 66),
('Diagramador', 66),
('Gerencia', 66),
('Vigilador', 66),
('Administrativo', 67),
('Diagramador', 67),
('Gerencia', 67),
('Vigilador', 67),
('Diagramador', 68),
('Gerencia', 68),
('Vigilador', 68),
('Gerencia', 69),
('Vigilador', 69),
('Gerencia', 70),
('Vigilador', 70),
('Gerencia', 71),
('Gerencia', 72),
('Diagramador', 73),
('Gerencia', 73),
('Vigilador', 73),
('Diagramador', 74),
('Gerencia', 74),
('Vigilador', 74),
('Gerencia', 75),
('Gerencia', 76),
('Gerencia', 77),
('Gerencia', 78),
('Gerencia', 79),
('Gerencia', 80),
('Gerencia', 81),
('Gerencia', 82),
('Gerencia', 83),
('Gerencia', 84),
('Gerencia', 85),
('Vigilador', 85),
('Administrativo', 107),
('Diagramador', 107),
('Gerencia', 107),
('Vigilador', 107),
('Diagramador', 1112),
('Gerencia', 1112),
('Diagramador', 3286),
('Gerencia', 3286),
('Diagramador', 4372),
('Gerencia', 4372),
('Gerencia', 7715),
('Gerencia', 8101),
('Gerencia', 8994),
('Gerencia', 9619),
('Gerencia', 9900),
('Gerencia', 10176),
('Gerencia', 12244),
('Vigilador', 12244),
('Gerencia', 12291),
('Vigilador', 12291),
('Gerencia', 12372),
('Vigilador', 12372),
('Gerencia', 12475),
('Vigilador', 12475),
('Gerencia', 13264),
('Gerencia', 13410),
('Gerencia', 13833),
('Gerencia', 14434),
('Gerencia', 15227),
('Vigilador', 15227),
('Gerencia', 15727),
('Gerencia', 15977),
('Gerencia', 16542),
('Gerencia', 17143),
('Gerencia', 17936),
('Administrativo', 24198),
('Diagramador', 24198),
('Gerencia', 24198),
('Vigilador', 24198),
('Administrativo', 24583),
('Diagramador', 24583),
('Gerencia', 24583),
('Vigilador', 24583),
('Administrativo', 24868),
('Diagramador', 24868),
('Gerencia', 24868),
('Vigilador', 24868),
('Administrativo', 25325),
('Gerencia', 25325),
('Administrativo', 26031),
('Diagramador', 26031),
('Gerencia', 26031),
('Vigilador', 26031),
('Gerencia', 27588),
('Administrativo', 28183),
('Diagramador', 28183),
('Gerencia', 28183),
('Gerencia', 34542),
('Gerencia', 39979),
('Gerencia', 42764),
('Gerencia', 63591),
('Vigilador', 63591),
('Gerencia', 63726),
('Vigilador', 63726),
('Gerencia', 63928),
('Vigilador', 63928),
('Gerencia', 66152),
('Vigilador', 66152),
('Gerencia', 66478),
('Gerencia', 69652),
('Gerencia', 69978),
('Gerencia', 75894),
('Administrativo', 81876),
('Diagramador', 81876),
('Gerencia', 81876),
('Administrativo', 82137),
('Gerencia', 82137),
('Diagramador', 111722),
('Gerencia', 111722),
('Diagramador', 116083),
('Gerencia', 116083),
('Diagramador', 116269),
('Gerencia', 116269),
('Vigilador', 116269),
('Diagramador', 116492),
('Gerencia', 116492),
('Vigilador', 116492),
('Diagramador', 116752),
('Gerencia', 116752),
('Diagramador', 117049),
('Gerencia', 117049),
('Vigilador', 117049),
('Gerencia', 126874),
('Gerencia', 127134),
('Gerencia', 130925),
('Gerencia', 131185),
('Gerencia', 137457),
('Gerencia', 137754),
('Administrativo', 144768),
('Gerencia', 144768),
('Administrativo', 144991),
('Diagramador', 144991),
('Gerencia', 144991),
('Vigilador', 144991),
('Gerencia', 205097),
('Gerencia', 246464),
('Vigilador', 246464),
('Gerencia', 246465),
('Vigilador', 246465),
('Gerencia', 289653),
('Vigilador', 289653),
('Gerencia', 327535),
('Vigilador', 327535);

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
) ENGINE=MyISAM AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4  ;

--
-- Volcado de datos para la tabla `rondas`
--

INSERT INTO `rondas` (`idRonda`, `puesto`, `objetivo_id`, `tipo`, `orden_escaneo`, `status`, `fecha_creacion`) VALUES
(34, 'Puesto 1 Notti', 12, 'Fija', 1, 'active', '2025-06-08 02:06:33'),
(35, 'Puesto 2 Notti', 12, 'Fija', 2, 'active', '2025-06-08 02:06:44'),
(36, 'Puesto 13 Notti', 12, 'Fija', 13, 'active', '2025-06-08 02:06:57'),
(37, 'Puesto 1 Central', 8, 'Fija', 15, 'active', '2025-06-08 02:13:41'),
(38, 'Puesto 2 Central', 8, 'Fija', 2, 'active', '2025-06-08 02:13:54'),
(41, 'Ronda Gerencia Notti', 12, 'Fija', 1, 'active', '2025-06-09 18:32:33'),
(42, 'Ronda Gerencia Notti 2 EDITADA', 12, 'Eventual', 2, 'inactive', '2025-06-09 18:32:59'),
(43, 'Ronda SUPER 1 EDITADA', 12, 'Fija', 1, 'inactive', '2025-06-09 19:11:48'),
(44, 'Ronda 2 SUPER 1 Notti', 12, 'Fija', 2, 'active', '2025-06-09 19:12:10'),
(45, 'Ronda 1 panaderia', 14, 'Fija', 1, 'active', '2025-06-12 19:24:31'),
(46, 'Ronda 2 Panaderia', 14, 'Fija', 2, 'active', '2025-06-12 19:24:39'),
(47, 'Ronda 3 panaderia', 14, 'Fija', 3, 'inactive', '2025-06-12 19:24:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

DROP TABLE IF EXISTS `turnos`;
CREATE TABLE IF NOT EXISTS `turnos` (
  `idTurno` int NOT NULL AUTO_INCREMENT,
  `objetivo_id` int DEFAULT NULL,
  `puesto_id` int DEFAULT NULL,
  `fecha` date NOT NULL,
  `turno` enum('Diurno','Nocturno') NOT NULL,
  `vigilador_id` int NOT NULL,
  `tipo_jornada` enum('Normal','Guardia Pasiva','Franco','Licencia') CHARACTER SET utf8mb4    NOT NULL,
  `is_referente` tinyint(1) NOT NULL DEFAULT '0',
  `entrada` time DEFAULT NULL,
  `salida` time DEFAULT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#FFFFFF',
  PRIMARY KEY (`idTurno`),
  KEY `objetivo_id` (`objetivo_id`),
  KEY `puesto_id` (`puesto_id`),
  KEY `vigilador_id` (`vigilador_id`)
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4  ;

--
-- Volcado de datos para la tabla `turnos`
--

INSERT INTO `turnos` (`idTurno`, `objetivo_id`, `puesto_id`, `fecha`, `turno`, `vigilador_id`, `tipo_jornada`, `is_referente`, `entrada`, `salida`, `color`) VALUES
(17, 12, 17, '2025-06-01', 'Diurno', 19, 'Normal', 0, '06:00:00', '18:00:00', '#ffffff'),
(18, 12, 18, '2025-06-01', 'Diurno', 23, 'Normal', 0, '06:00:00', '18:00:00', '#ffffff'),
(19, 12, 19, '2025-06-01', 'Nocturno', 24, 'Normal', 0, '18:00:00', '06:00:00', '#ffffff'),
(20, 12, 20, '2025-06-01', 'Diurno', 25, 'Franco', 0, '06:00:00', '18:00:00', '#ffffff'),
(21, 12, 20, '2025-06-01', 'Diurno', 26, 'Normal', 0, '06:00:00', '18:00:00', '#f60909'),
(22, 12, 20, '2025-06-01', 'Diurno', 27, 'Franco', 0, '06:00:00', '18:00:00', '#ffffff'),
(27, 12, 22, '2025-06-01', 'Diurno', 31, 'Franco', 0, '06:00:00', '18:00:00', '#ffffff'),
(24, 12, 20, '2025-06-01', 'Diurno', 28, 'Franco', 0, '06:00:00', '18:00:00', '#ffffff'),
(25, 12, 21, '2025-06-01', 'Nocturno', 29, 'Normal', 0, '18:00:00', '06:00:00', '#fa0000'),
(26, 12, 21, '2025-06-01', 'Nocturno', 30, 'Normal', 0, '18:00:00', '06:00:00', '#ffffff'),
(28, 12, 22, '2025-06-01', 'Diurno', 32, 'Normal', 0, '06:00:00', '18:00:00', '#ffffff'),
(29, 7, 1, '2025-06-07', 'Diurno', 63, 'Normal', 0, '06:00:00', '18:00:00', '#ffffff'),
(30, 15, 27, '2025-06-10', 'Diurno', 18, 'Normal', 0, '06:00:00', '18:00:00', '#ffffff'),
(31, 15, 27, '2025-06-10', 'Nocturno', 68, 'Normal', 0, '18:00:00', '06:00:00', '#ffffff'),
(32, 15, 27, '2025-06-11', 'Diurno', 68, 'Franco', 0, '06:00:00', '18:00:00', '#ffffff'),
(33, 15, 27, '2025-06-11', 'Nocturno', 18, 'Normal', 0, '18:00:00', '06:00:00', '#ffffff');

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
  `rol` varchar(30) CHARACTER SET utf8mb4    NOT NULL,
  `imgPerfil` varchar(60) DEFAULT NULL,
  `imgRepriv` varchar(60) DEFAULT NULL,
  `resetPass` int NOT NULL,
  `activo` int NOT NULL,
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idUsuario`),
  UNIQUE KEY `dni` (`dni`)
) ENGINE=MyISAM AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4  ;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idUsuario`, `nombre`, `apellido`, `dni`, `pass`, `f_nac`, `telefono`, `tel_emergencia`, `nombre_contacto`, `parentesco`, `domicilio`, `provincia`, `rol`, `imgPerfil`, `imgRepriv`, `resetPass`, `activo`, `fecha_creacion`) VALUES
(5, 'Administrador', 'Admin', '12456789', '$2y$10$rxO8nd1EIiK7pFH8lh5GR.4rstP2hP5NXB2QXc.VRaijiMQ9eHnau', '2000-03-15', '2613334444', '2615553333', 'Juan', 'Hijo', 'Av Siempre Viva 123', 'Mendoza', 'Gerencia', '', 'img/repriv/AdminAdminRepriv.png', 1, 1, '2025-03-15 21:22:52'),
(7, 'J', 'VERA', '11111111', '$2y$10$sLSgXzcZcddTV/H2MgghiuslZDGklMOfMvro6CV4aD8akBaje3Ld6', '2000-01-01', '2616666333', '2615553333', '', '', 'Chuquisaca 740', 'Mendoza', 'Vigilador', 'img/perfil/JVERAPerfil.png', 'img/repriv/JVERARepriv.png', 1, 1, '2025-05-16 23:51:05'),
(8, 'J', 'RODRIGUEZ', '22222222', '$2y$10$XSc00qKXmDKNHQq8NjeyF.tehBriNsRV2IUS1h150upVXPh/CYL4q', '2001-01-01', '2634555666', '2615553333', '', '', 'Calle Falsa 123', 'Mendoza', 'Vigilador', 'img/perfil/JRODRIGUEZPerfil.png', 'img/repriv/JRODRIGUEZRepriv.png', 1, 1, '2025-05-16 23:51:59'),
(9, 'M', 'LUCERO', '3333333', '$2y$10$Z8m4wQI3qB40uwAlwFhfOeV2ddvoFLqOalaos8fxL6CLtPtq6Cpdq', '2000-01-02', '2616663333', '2615553333', '', '', 'Entre Rios 158', 'Mendoza', 'Vigilador', 'img/perfil/MLUCEROPerfil.png', 'img/repriv/MLUCERORepriv.png', 1, 1, '2025-05-16 23:53:45'),
(10, 'A', 'QUIROGA', '33322224', '$2y$10$MtnJHg2yVnGDi4A5Dt71W.GdCmuM8uhbgcdlKK9gdw/QjVA2NfnpK', '2000-01-03', '7894513', '2615553333', '', '', 'Chuquisaca 740', 'Mendoza', 'Vigilador', 'img/perfil/AQUIROGAPerfil.png', 'img/repriv/AQUIROGARepriv.png', 1, 1, '2025-05-16 23:55:09'),
(11, 'M', 'SOSA', '44444444', '$2y$10$vT5HkRdTxIubJWzA8biShONC.3q/P3HpRksqL9qLB7okom6nfM4B2', '2004-01-04', '2613334444', '2615553333', '', '', 'Av Siempre Viva 123', 'Mendoza', 'Vigilador', 'img/perfil/MSOSAPerfil.png', 'img/repriv/MSOSARepriv.png', 1, 1, '2025-05-16 23:56:03'),
(12, 'F', 'FERRIGNO', '55505555', '$2y$10$WioP3xh07BSzyBtU5ONMLunzRPdzX2iBhAuK9wO.3nN5p5djx60/2', '2000-01-05', '2616666333', '2615553333', '', '', 'Calle Falsa 123', 'Mendoza', 'Vigilador', 'img/perfil/FFERRIGNOPerfil.png', 'img/repriv/FFERRIGNORepriv.png', 1, 1, '2025-05-16 23:56:38'),
(13, 'N', 'LUCERO', '12355555', '$2y$10$qZdda6tvUrra4yROVCldf.go5DAsllCahwG86lHABbWUkGTEoeEqi', '2000-01-05', '2616663333', '2615553333', '', '', 'Entre Rios 158', 'Mendoza', 'Vigilador', 'img/perfil/NLUCEROPerfil.png', 'img/repriv/NLUCERORepriv.png', 1, 1, '2025-05-16 23:57:25'),
(14, 'L', 'MORALES', '32111111', '$2y$10$nGzHpdxmEhc7VYDgo8Bh4./ihvdL.1FBnP0JYCTYyLHp1vq6/rx1.', '1999-04-04', '2616663333', '2615553333', '', '', 'Entre Rios 158', 'Chubut', 'Vigilador', 'img/perfil/LMORALESPerfil.png', 'img/repriv/LMORALESRepriv.png', 1, 1, '2025-05-16 23:58:52'),
(15, 'C', 'CARRAL', '20111111', '$2y$10$miDzWlNaNzdQqRr4WCpW8uf412GjWIPAZif9Y5T1K5Y5QiaroVkza', '1998-08-05', '2616666333', '2615553333', '', '', 'Calle Falsa 123', 'Córdoba', 'Vigilador', 'img/perfil/CCARRALPerfil.png', 'img/repriv/CCARRALRepriv.png', 1, 1, '2025-05-16 23:59:35'),
(16, 'C', 'CAPADONA', '30222222', '$2y$10$OayoqEwC8WfM5xjJdbDRKew5QSUXXwWdiT7KQSGoR26Ytjjj6Qjpe', '1998-08-05', '2613334444', '2615553333', '', '', 'Av Siempre Viva 123', 'Mendoza', 'Vigilador', 'img/perfil/CCAPADONAPerfil.png', 'img/repriv/CCAPADONARepriv.png', 1, 1, '2025-05-17 00:00:22'),
(17, 'G', 'GODOY', '20555744', '$2y$10$aeVmnoINJ/lre0YFs82yFucTxww194fsg3p/xn84lYxSuN3Oek4pq', '1975-06-05', '2613334444', '2615553333', '', '', 'Av Siempre Viva 123', 'Formosa', 'Vigilador', 'img/perfil/GGODOYPerfil.png', 'img/repriv/GGODOYRepriv.png', 1, 1, '2025-05-17 00:01:08'),
(18, 'Javier', 'Pineda', '31816334', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1985-10-17', '2616666333', '2615553333', 'Juan Perez', 'VECINO', 'Av Siempre Viva 123', 'Mendoza', 'Vigilador', 'img/perfil/JavierPinedaPerfil.png', 'img/repriv/JavierPinedaRepriv.png', 1, 1, '2025-05-22 17:20:50'),
(19, 'Gonzalo', 'Araujo', '30000001', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000001', '3701000001', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/GonzaloAraujoPerfil.png', 'img/repriv/GonzaloAraujoRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(20, 'Dante', 'Barrera', '30000002', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000002', '3701000002', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/DanteBarreraPerfil.png', 'img/repriv/DanteBarreraRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(21, 'Angel', 'Britez', '30000003', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000003', '3701000003', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/AngelBritezPerfil.png', 'img/repriv/AngelBritezRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(22, 'Lucas', 'Bustos', '30000004', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000004', '3701000004', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/LucasBustosPerfil.png', 'img/repriv/LucasBustosRepriv.png', 1, 0, '2025-06-06 13:00:00'),
(23, 'Facaundo', 'Candido', '30000005', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000005', '3701000005', 'Zaraza', 'Madre', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/FacaundoCandidoPerfil.png', 'img/repriv/FacaundoCandidoRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(24, 'Adrian', 'Castro', '30000006', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000006', '3701000006', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/AdrianCastroPerfil.png', 'img/repriv/AdrianCastroRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(25, 'Abel', 'Cayo', '30000007', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000007', '3701000007', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/AbelCayoPerfil.png', 'img/repriv/AbelCayoRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(26, 'Yago', 'Chaparro', '30000008', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000008', '3701000008', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/YagoChaparroPerfil.png', 'img/repriv/YagoChaparroRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(27, 'Diego', 'Funes', '30000009', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000009', '3701000009', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/DiegoFunesPerfil.png', 'img/repriv/DiegoFunesRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(28, 'Lucas', 'Gnappa', '30000010', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000010', '3701000010', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/LucasGnappaPerfil.png', 'img/repriv/LucasGnappaRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(29, 'Rodrigo', 'Gonzalez', '30000011', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000011', '3701000011', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/RodrigoGonzalezPerfil.png', 'img/repriv/RodrigoGonzalezRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(30, 'Emanuel', 'Guiñazu', '30000012', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000012', '3701000012', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/EmanuelGuiñazuPerfil.png', 'img/repriv/EmanuelGuiñazuRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(31, 'Esteban', 'Ibañez', '30000013', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000013', '3701000013', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/EstebanIbañezPerfil.png', 'img/repriv/EstebanIbañezRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(32, 'Agustin', 'Lucero', '30000014', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000014', '3701000014', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/AgustinLuceroPerfil.png', 'img/repriv/AgustinLuceroRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(33, 'Maurico', 'Merlo', '30000015', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000015', '3701000015', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/MauricoMerloPerfil.png', 'img/repriv/MauricoMerloRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(34, 'Christopher', 'Nievas', '30000016', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000016', '3701000016', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/ChristopherNievasPerfil.png', 'img/repriv/ChristopherNievasRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(35, 'Jorge', 'Ontivero', '30000017', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000017', '3701000017', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/JorgeOntiveroPerfil.png', 'img/repriv/JorgeOntiveroRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(36, 'Diego', 'Ordoñez', '30000018', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000018', '3701000018', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/DiegoOrdoñezPerfil.png', 'img/repriv/DiegoOrdoñezRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(37, 'Emanuel', 'Pajares', '30000019', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000019', '3701000019', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/EmanuelPajaresPerfil.png', 'img/repriv/EmanuelPajaresRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(38, 'Alexis', 'Palleros', '30000020', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000020', '3701000020', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/AlexisPallerosPerfil.png', 'img/repriv/AlexisPallerosRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(39, 'Sebastian', 'Ponce', '30000021', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000021', '3701000021', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/SebastianPoncePerfil.png', 'img/repriv/SebastianPonceRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(40, 'Gustavo', 'Roque', '30000022', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000022', '3701000022', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/GustavoRoquePerfil.png', 'img/repriv/GustavoRoqueRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(41, 'Sebastian', 'Ruiz', '30000023', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000023', '3701000023', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/SebastianRuizPerfil.png', 'img/repriv/SebastianRuizRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(42, 'Karin', 'Salinas', '30000024', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000024', '3701000024', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/KarinSalinasPerfil.png', 'img/repriv/KarinSalinasRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(43, 'Felix', 'Valdez', '30000025', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000025', '3701000025', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/FelixValdezPerfil.png', 'img/repriv/FelixValdezRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(44, 'Agustin', 'Vazquez', '30000026', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000026', '3701000026', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/AgustinVazquezPerfil.png', 'img/repriv/AgustinVazquezRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(45, 'Leonardo', 'Vegas', '30000027', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000027', '3701000027', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/LeonardoVegasPerfil.png', 'img/repriv/LeonardoVegasRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(46, 'Pablo', 'Vela', '30000028', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000028', '3701000028', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/PabloVelaPerfil.png', 'img/repriv/PabloVelaRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(47, 'Nicolas', 'Videla', '30000029', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000029', '3701000029', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/NicolasVidelaPerfil.png', 'img/repriv/NicolasVidelaRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(48, 'Maximiliano', 'Arguello', '30000030', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000030', '3701000030', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/MaximilianoArguelloPerfil.png', 'img/repriv/MaximilianoArguelloRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(49, 'Lucas', 'Barrionuevo', '30000031', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000031', '3701000031', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/LucasBarrionuevoPerfil.png', 'img/repriv/LucasBarrionuevoRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(50, 'Renzo', 'Berardy', '30000032', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-10-17', '3700000032', '3701000032', 'Marta', 'Madre', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/RenzoBerardyPerfil_20250607104852.webp', 'img/repriv/RenzoBerardyRepriv_20250607104852.png', 0, 1, '2025-06-06 13:00:00'),
(51, 'Lucas', 'Castillo', '30000033', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000033', '3701000033', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/LucasCastilloPerfil.png', 'img/repriv/LucasCastilloRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(52, 'Carlos', 'Figueroa', '30000034', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000034', '3701000034', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/CarlosFigueroaPerfil.png', 'img/repriv/CarlosFigueroaRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(53, 'Ariel', 'Guardias', '30000035', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000035', '3701000035', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/ArielGuardiasPerfil.png', 'img/repriv/ArielGuardiasRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(54, 'Sol', 'Alvarez', '30000036', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000036', '3701000036', 'Marta', 'Madre', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/SolAlvarezPerfil.png', 'img/repriv/SolAlvarezRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(55, 'Jenifer', 'Cano', '30000037', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000037', '3701000037', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/JeniferCanoPerfil.png', 'img/repriv/JeniferCanoRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(56, 'Lorena', 'Fredes', '30000038', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000038', '3701000038', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/LorenaFredesPerfil.png', 'img/repriv/LorenaFredesRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(57, 'Monica', 'Muñoz', '30000039', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000039', '3701000039', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/MonicaMuñozPerfil.png', 'img/repriv/MonicaMuñozRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(58, 'Florencia', 'Rodriguez', '30000040', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000040', '3701000040', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/FlorenciaRodriguezPerfil.png', 'img/repriv/FlorenciaRodriguezRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(59, 'Tania', 'Rojas', '30000041', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000041', '3701000041', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/TaniaRojasPerfil.png', 'img/repriv/TaniaRojasRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(60, 'Blanca', 'Quiroga', '30000042', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000042', '3701000042', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/BlancaQuirogaPerfil.png', 'img/repriv/BlancaQuirogaRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(61, 'Marcela', 'Vove', '30000043', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000043', '3701000043', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/MarcelaVovePerfil.png', 'img/repriv/MarcelaVoveRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(62, 'Sonia', 'Vardaguer', '30000044', '$2y$10$q6HrvOV0/4XSRhydR8R7w.lUU9rPBdRvtSO8GZ6QRYIYXLnGImDs.', '1990-01-01', '3700000044', '3701000044', '', '', 'Calle Falsa 123', 'Buenos Aires', 'Vigilador', 'img/perfil/SoniaVardaguerPerfil.png', 'img/repriv/SoniaVardaguerRepriv.png', 1, 1, '2025-06-06 13:00:00'),
(63, 'Alejandro', 'Albornoz', '93456789', '$2y$10$ZTnmY097ajaqQSKIdN4wBOxct47d2uaUDFRAHnasSXTV4BesxWYm2', '1980-10-03', '2616666333', '2615553333', 'Juan', 'Padre', 'Chuquisaca 740', 'Mendoza', 'Vigilador', NULL, 'img/repriv/AlejandroAlbornozRepriv.png', 1, 1, '2025-06-06 13:28:03'),
(64, 'Supervisor 1', 'Prueba', '77777777', '$2y$10$ixOEHL4dy01TCeFbWswAcusl8677mejszQ6P9R4YnyalbOdEuh87C', '1990-10-01', '2613334444', '2615553333', 'Zaraza', 'Madre', 'Calle de prueba', 'Catamarca', 'Supervisor 1', 'img/perfil/Supervisor1PruebaPerfil_20250608202122.png', 'img/repriv/Supervisor1PruebaRepriv_20250608202122.png', 1, 1, '2025-06-08 23:21:22'),
(65, 'Supervisor 2', 'Prueba', '88888888', '$2y$10$192LdvSo6W7o8xGjjqNo1.FcN8nxl4gt9nSwD3shsc0h50DVXQY4m', '1990-10-01', '2616663333', '3701000032', 'Juan Perez', 'Hijo', 'Calle de prueba', 'Misiones', 'Supervisor 2', NULL, NULL, 1, 1, '2025-06-08 23:25:20'),
(66, 'Diagramador', 'Pruebe', '99999999', '$2y$10$yC2UtG7zgku2evYB/r.44uqubQsM32s1d7bihd2TyOHugJwSL/oc6', '1990-10-10', '2613334444', '3701000032', 'Zaraza', 'Primo', 'Calle de prueba', 'Neuquén', 'Diagramador', 'img/perfil/DiagramadorPruebePerfil_20250608203203.png', '', 1, 1, '2025-06-08 23:30:49'),
(67, 'Administrativos', 'Prueba', '10000000', '$2y$10$ieymvlGLnqRtc1o32B057.eitbhF5949xEPdg/iC5tC7.tpXr2rcy', '1990-10-10', '2613334444', '2615553333', 'Zaraza', 'Hijo', 'Calle de prueba', 'Neuquén', 'Administrativo', 'img/perfil/AdministrativosPruebaPerfil_20250608203132.webp', NULL, 1, 1, '2025-06-08 23:31:32'),
(68, 'Vigilador', 'Prueba MODIFICADO', '55555555', '$2y$10$pET4ygCKgNOUh1iyJkgr8uwj53w.dc5cdfAMsXoMz33um/jVKG7Z.', '1990-10-01', '2613334444', '2615553333', 'Zaraza', 'Primo', 'Calle de prueba', 'Chaco', 'Vigilador', '', '', 1, 1, '2025-06-08 23:53:15');

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
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4  ;

--
-- Volcado de datos para la tabla `usuario_objetivo`
--

INSERT INTO `usuario_objetivo` (`id`, `usuario_id`, `objetivo_id`, `fecha`) VALUES
(1, 31, 12, '2025-06-01'),
(2, 32, 12, '2025-06-01'),
(3, 63, 7, '2025-06-07'),
(4, 18, 15, '2025-06-10'),
(5, 68, 15, '2025-06-10'),
(6, 68, 15, '2025-06-11'),
(7, 18, 15, '2025-06-11');

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4  ;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
