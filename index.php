<?php
session_start();  // Aseguramos que la sesión esté iniciada para poder verificar $_SESSION
//session_destroy();
require_once("config.php");
require_once('controladores/archivos.controller.php');
require_once('controladores/cronograma.controller.php');
require_once("controladores/directivas.controller.php");
require_once('controladores/hvivo.controller.php');
require_once('controladores/login.controller.php');
require_once("controladores/mensajes.controller.php");
require_once("controladores/novedades.controller.php");
require_once("controladores/objetivos.controller.php");
require_once("controladores/plantilla.controller.php");
require_once("controladores/puestos.controller.php");
require_once("controladores/qr.controller.php");
require_once("controladores/rondas.controller.php");
require_once("controladores/rutas.controller.php");
require_once("controladores/turnos.controller.php");
require_once("controladores/usuarios.controller.php");


// Comprobamos si el usuario está autenticado
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
