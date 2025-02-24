<?php
require_once("config.php");
require_once("controladores/rutas.controller.php");
require_once("controladores/plantilla.controller.php");
require_once("controladores/usuarios.controller.php");
require_once("controladores/objetivos.controller.php");
require_once("controladores/mensajes.controller.php");
require_once("controladores/rondas.controller.php");
require_once("controladores/qr.controller.php");



$plantilla= new PlantillaController();
 // ejecutar metodo
$plantilla->crtGetPlantilla();


?>