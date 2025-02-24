<?php

class QrModel {

    public function guardarQrEnSesion($qr) {
        if(!isset($_SESSION['qr_codes']) || !is_array($_SESSION['qr_codes'])){
            $_SESSION['qr_codes'] = [];
        }
        $_SESSION['qr_codes'][] = $qr;
    }
    public function guardarTodosEnBD() {
        // Aquí implementa la conexión a la BD y la inserción de los QR almacenados en la sesión.
    }
}
?>
