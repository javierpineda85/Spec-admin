<?php

$_SESSION = [];           // Vaciar todas las variables
session_unset();          // Liberar variables de sesión
session_destroy();        // Destruir la sesión en el servidor

// Opcional: borrar la cookie de sesión (PHP usa cookies propias)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

header("Location: index.php");
exit;


?>