<?php
unset($_SESSION['permisos_usuario']);
session_reset();
session_unset();
session_destroy();
header('location:index.php');

?>