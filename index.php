<?php
session_start();  // Aseguramos que la sesión esté iniciada para poder verificar $_SESSION
date_default_timezone_set('America/Argentina/Mendoza');
setlocale(LC_TIME, 'es_AR.UTF-8', 'spanish');

// Core y middlewares
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/CheckPermissionMiddleware.php';

// Config global
require_once __DIR__ . '/config.php';

// Controladores base
require_once('controladores/alertas.controller.php');
require_once('controladores/archivos.controller.php');
require_once('controladores/art.controller.php');
require_once('controladores/config.controller.php');
require_once('controladores/cronograma.controller.php');
require_once('controladores/datospersonales.controller.php');
require_once("controladores/directivas.controller.php");
require_once("controladores/escaneos.controller.php");
require_once("controladores/feriados.controller.php");
require_once('controladores/hvivo.controller.php');
require_once('controladores/login.controller.php');
require_once('controladores/marcaciones.controller.php');
require_once("controladores/mensajes.controller.php");
require_once("controladores/noticias.controller.php");
require_once("controladores/notificaciones.controller.php");
require_once("controladores/novedades.controller.php");
require_once("controladores/objetivos.controller.php");
require_once("controladores/permisos.controller.php");
require_once("controladores/plantilla.controller.php");
require_once("controladores/puestos.controller.php");
require_once("controladores/qr.controller.php");
require_once("controladores/resetpassword.controller.php");
require_once("controladores/roles.controller.php");
require_once("controladores/rondas.controller.php");
require_once("controladores/rutas.controller.php");
require_once("controladores/salud.controller.php");
require_once("controladores/toastify.controller.php");
require_once("controladores/turnos.controller.php");
require_once("controladores/uniformes.controller.php");
require_once("controladores/usuarios.controller.php");

// Si no es la ruta de login (GET o POST), exigimos autenticación
// ⑤ Detectamos la ruta solicitada (p.ej. ?r=login, ?r=reset-password)
$r = $_GET['r'] ?? '';

// ===== 1) RESTABLECER CONTRASEÑA =====
// Ruta libre: muestra el formulario o procesa el POST
if ($r === 'reset-password') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        ResetPasswordController::crtResetPassword();
    } else {
        ResetPasswordController::vistaResetPassword();
    }
    exit;
}

// ===== 2) LOGIN =====
// Ruta libre: muestra el formulario o procesa el POST
if ($r === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        LoginController::procesarLogin();
    } else {
        (new LoginController())->mostrarLogin();
    }
    exit;
}

// ===== 3) AJAX – ejemplo de reporte “hombre vivo” =====
// Ejecución rápida de un endpoint JSON
if ($r === 'registrar_reporte') {
    header('Content-Type: application/json; charset=utf-8');
    HombreVivoController::ajaxRegistrarReporte();
    exit;
}
// ===== 3b) AJAX – auto rotar equitativo =====
if ($r === 'auto_rotar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    ControladorPuestos::crtAutoRotarEquitativo();
    exit;
}

// ===== 4) PROTECCIÓN GENERAL =====
// A partir de aquí, cualquier otra ruta exige usuario autenticado
Auth::requireLogin();


if (!isset($_SESSION['idUsuario']) || empty($_SESSION['idUsuario'])) {
    // Si no está autenticado, redirigimos al login
    // Aquí puedes hacer una redirección o mostrar la vista de login
    if (isset($_GET['r']) && $_GET['r'] != 'login') {
        // Si hay alguna ruta diferente de 'login', redirigir al login
        header("Location: index.php?r=login");
        exit();
    } else {
        // Si ya estamos en la ruta de login o no hay ruta, mostrar login
        $loginController = new LoginController();
        $loginController->mostrarLogin();
        exit();
    }
} else {
    // Si el usuario está autenticado, procesar las rutas de la aplicación
    if (isset($_GET['r'])) {
        // Si hay una ruta específica
        $plantilla = new PlantillaController();
        $plantilla->crtGetPlantilla();
    } else {
        // Si no se especifica ruta, cargar la vista predeterminada (inicio)

        $plantilla = new PlantillaController();
        $plantilla->crtGetPlantilla();
    }
}
