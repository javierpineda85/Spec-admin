<?php

// Registrar marcación (entrada o salida)
if (isset($_GET['r']) && $_GET['r'] === 'registrar_marcacion') {
    MarcacionesController::crtRegistrarMarcacion();
    return;
}
