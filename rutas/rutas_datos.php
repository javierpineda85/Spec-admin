<?php

if (isset($_GET['r']) && $_GET['r'] === 'mis_datos_personales') {
    DatosPersonalesController::vistaMisDatosPersonales();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'mi_salud') {
    SaludController::vistaMiSalud();
    return;
}