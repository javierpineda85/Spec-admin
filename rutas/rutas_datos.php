<?php

if (isset($_GET['r']) && $_GET['r'] === 'mis_datos_personales') {
    DatosPersonalesController::vistaMisDatosPersonales();
    define('RUTA_EJECUTADA', true);
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'mi_salud') {
    SaludController::vistaMiSalud();
    define('RUTA_EJECUTADA', true);
    return;
}