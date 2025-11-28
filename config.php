<?php
/* esto lo usamos para usar la rutas correctamentes sin .php  y que tome siempre el index
al no colocar nada en la url*/

// Obtiene algo como “/Spec-admin” o “” si estás en la raíz
$baseUrl = rtrim(
    str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])),
    '/\\'
);

// Define la constante que usarás en tus plantillas
if (!defined('BASE_URL')) {
    $baseUrl = rtrim(
        str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])),
        '/\\'
    );
    define('BASE_URL', $baseUrl);
}

// Zona horaria
date_default_timezone_set('America/Argentina/Mendoza');

//Carga de datos de configuración
require_once('modelos/config.modelo.php');
$configModel = new Configuracion();
global $config;
$config = [];
foreach ($configModel->obtenerTodo() as $row) {
    $config[$row['clave']] = $row['valor'];
}
