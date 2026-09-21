<?php
// Compatibilidad con enlaces antiguos: misma autenticación y reglas del front controller.
chdir(dirname(__DIR__, 3));
$_GET['r'] = 'validar_turno_global';
require 'index.php';
