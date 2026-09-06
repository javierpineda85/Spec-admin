<?php

if (isset($_GET['r']) && $_GET['r'] === 'cumpleanos') {
    NoticiasController::vistaCumple();
    define('RUTA_EJECUTADA', true);
    return;
}
