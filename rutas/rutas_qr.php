<?php

if (isset($_GET['r']) && $_GET['r'] === 'mostrar_qr') {
    QrController::mostrar();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'delete_qr') {
    QrController::delete();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'generar_qr') {
    QrController::generar();
    return;
}