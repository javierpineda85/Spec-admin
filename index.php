<?php
session_start();  // Aseguramos que la sesión esté iniciada para poder verificar $_SESSION
//session_destroy();
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/CheckPermissionMiddleware.php';
require_once("config.php");
require_once('controladores/alertas.controller.php');
require_once('controladores/archivos.controller.php');
require_once('controladores/bajas.controller.php');
require_once('controladores/cronograma.controller.php');
require_once("controladores/directivas.controller.php");
require_once("controladores/escaneos.controller.php");
require_once('controladores/hvivo.controller.php');
require_once('controladores/login.controller.php');
require_once('controladores/marcaciones.controller.php');
require_once("controladores/mensajes.controller.php");
require_once("controladores/novedades.controller.php");
require_once("controladores/objetivos.controller.php");
require_once("controladores/permisos.controller.php");
require_once("controladores/plantilla.controller.php");
require_once("controladores/puestos.controller.php");
require_once("controladores/qr.controller.php");
require_once("controladores/rondas.controller.php");
require_once("controladores/rutas.controller.php");
require_once("controladores/turnos.controller.php");
require_once("controladores/usuarios.controller.php");



// Si no es la ruta de login (GET o POST), exigimos autenticación
$r = $_GET['r'] ?? '';
if ($r !== 'login') {
    Auth::requireLogin();
}
//colocamos el handler acá para que cargue antes que la vista
if (isset($_GET['r']) && $_GET['r'] === 'registrar_reporte') {
    header('Content-Type: application/json; charset=utf-8');
    require_once __DIR__ . '/controladores/hvivo.controller.php';
    HombreVivoController::ajaxRegistrarReporte();
    exit;  // importantísimo para que no siga al resto
}
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
        $plantilla ->crtGetPlantilla();
    } else {
        // Si no se especifica ruta, cargar la vista predeterminada (inicio)
        
        $plantilla = new PlantillaController();
        $plantilla ->crtGetPlantilla();
    }
}
