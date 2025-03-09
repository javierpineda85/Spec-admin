<?php
$_SESSION['usuario_id']=2; //Borrar una vez que esté logueado el usuario
// Crea una instancia del controlador y llama al método generar
$reporte = new hVivo();
$reporte->registrar();
?>