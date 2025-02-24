<?php

require_once('controladores/qr.controller.php');

// Crea una instancia del controlador y llama al método generar
$qrController = new QrController();
$qrController->generar();
?>