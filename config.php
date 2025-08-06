<?php
/* esto lo usamos para usar la rutas correctamentes sin .php  y que tome siempre el index
al no colocar nada en la url*/

// Obtiene algo como “/Spec-admin” o “” si estás en la raíz
$baseUrl = rtrim(
    str_replace('\\','/', dirname($_SERVER['SCRIPT_NAME'])),
    '/\\'
);

// Define la constante que usarás en tus plantillas
define('BASE_URL', $baseUrl);

// Zona horaria
date_default_timezone_set('America/Argentina/Mendoza');

?>