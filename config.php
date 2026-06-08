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

// Claves VAPID para Web Push.
// En produccion conviene mover esto a variables de entorno o a un archivo
// de configuracion fuera del repositorio.
if (!defined('PUSH_VAPID_PUBLIC_KEY')) {
    define('PUSH_VAPID_PUBLIC_KEY', 'BC0BXuMrwfUNdXmUCHMumHPWJU9oG0WVm-5cHyag-WtCkK-OG8CrDXj0baMPDOAD_oXwa_odle87wWSLAdE6fzQ');
}

if (!defined('PUSH_VAPID_PRIVATE_KEY_PEM')) {
    define('PUSH_VAPID_PRIVATE_KEY_PEM', <<<'PEM'
-----BEGIN PRIVATE KEY-----
MIGHAgEAMBMGByqGSM49AgEGCCqGSM49AwEHBG0wawIBAQQg0JMeC/L40Hmr7njN
wPsDyzFrjtF89xgGd3NugeZi0/qhRANCAAQtAV7jK8H1DXV5lAhzLphz1iVPaBtF
lZvuXB8moPlrQpCvjhvAqw149G2jDwzgA/6F8Gv6HZXvO8FkiwHROn80
-----END PRIVATE KEY-----
PEM);
}

if (!defined('PUSH_VAPID_SUBJECT')) {
    define('PUSH_VAPID_SUBJECT', 'mailto:soporte@spec-admin.local');
}
