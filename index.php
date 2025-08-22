<?php
session_start();  // Aseguramos que la sesión esté iniciada para poder verificar $_SESSION
//session_destroy();
//Evitar problemas con la caché en el navegador
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

//Para rutas amigables sin index.php?r=crearAlgo en la URL
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'); // ej: /Spec-admin
$uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // lo que ve el navegador (sin query)

// Si vino con ?r=..., armamos la URL "bonita"
if (isset($_GET['r'])) {
    $ruta = trim($_GET['r'], '/');

    // reconstruir el resto de parámetros (excepto r)
    $qs = $_GET;
    unset($qs['r']);
    $suffix = '';
    if (!empty($qs)) {
        $suffix = '?' . http_build_query($qs);
    }

    // URL bonita objetivo
    $pretty = rtrim($base, '/') . '/' . $ruta;

    // **Clave**: solo redirigimos si el path *visible* NO es ya la URL bonita
    if ($uriPath !== $pretty) {
        header('Location: ' . $pretty . $suffix, true, 301);
        exit;
    }
}
date_default_timezone_set('America/Argentina/Mendoza');
setlocale(LC_TIME, 'es_AR.UTF-8', 'spanish');
require_once __DIR__ . '/core/Auth.php';
require_once __DIR__ . '/core/CheckPermissionMiddleware.php';
require_once("config.php");
require_once('controladores/alertas.controller.php');
require_once('controladores/archivos.controller.php');
require_once('controladores/art.controller.php');
require_once('controladores/bajas.controller.php');
require_once('controladores/cronograma.controller.php');
require_once('controladores/datospersonales.controller.php');
require_once("controladores/directivas.controller.php");
require_once("controladores/escaneos.controller.php");
require_once("controladores/feriados.controller.php");
require_once('controladores/hvivo.controller.php');
//require_once('controladores/legajos.controller.php');
require_once('controladores/login.controller.php');
require_once('controladores/marcaciones.controller.php');
require_once("controladores/mensajes.controller.php");
require_once("controladores/noticias.controller.php");
require_once("controladores/novedades.controller.php");
require_once("controladores/objetivos.controller.php");
require_once("controladores/permisos.controller.php");
require_once("controladores/plantilla.controller.php");
require_once("controladores/puestos.controller.php");
require_once("controladores/qr.controller.php");
require_once("controladores/roles.controller.php");
require_once("controladores/rondas.controller.php");
require_once("controladores/rutas.controller.php");
require_once("controladores/salud.controller.php");
require_once("controladores/toastify.controller.php");
require_once("controladores/turnos.controller.php");
require_once("controladores/uniformes.controller.php");
require_once("controladores/usuarios.controller.php");



// Si no es la ruta de login (GET o POST), exigimos autenticación
$r = $_GET['r'] ?? '';
if ($r === '') {
    header('Location:index.php?r=login', true, 302);
    exit;
}
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
        $plantilla->crtGetPlantilla();
    } else {
        // Si no se especifica ruta, cargar la vista predeterminada (inicio)

        $plantilla = new PlantillaController();
        $plantilla->crtGetPlantilla();
    }
}
