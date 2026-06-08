<?php
require_once "../controladores/mensajes.controller.php";

if (isset($_POST['idMensaje'])) {
  $id = intval($_POST['idMensaje']);
  $exito = ControladorMensajes::crtMarcarLeido($id);
  echo json_encode(['exito' => $exito]);
} else {
  echo json_encode(['exito' => false, 'error' => 'ID no recibido']);
}
