<?php

// Incluye el modelo que contiene la lógica de autenticación
require_once('modelos/usuarios.modelo.php');

class LoginController
{
    private $modeloUsuarios;

    public function __construct()
    {
        $this->modeloUsuarios = new ModeloUsuarios();
    }

    public function mostrarLogin()
    {
        include_once('vistas/login.php');
    }

    public static function procesarLogin()
    {
       
        // Comprobamos si se han enviado los datos por el formulario
        if (isset($_POST['dni']) && isset($_POST['pass'])) {
            $dni = $_POST['dni']; // Recibimos el DNI
            $password = $_POST['pass']; // Recibimos la contraseña

            // Crear una instancia del modelo de usuarios de manera estática
            $modeloUsuarios = new ModeloUsuarios();
            // Llamamos al método del modelo para autenticar al usuario
            $esAutenticado = $modeloUsuarios->authenticate($dni, $password);
           
            if ($esAutenticado) {
                // Si el inicio de sesión es exitoso, guardar los datos en la sesión
                $_SESSION['idUsuario'] = $esAutenticado[0]['idUsuario'];
                $_SESSION['nombre'] = $esAutenticado[0]['nombre'];
                $_SESSION['apellido'] = $esAutenticado[0]['apellido'];
                $_SESSION['imgPerfil'] = $esAutenticado[0]['imgPerfil'];
                // Redirigir al inicio
                header('Location: index.php');
                exit();
            } else {
                // Si no se ha podido autenticar, mostrar un error
                $_SESSION['success_message']= "DNI o contraseña incorrectos.";
            }
        } else {
            // Si no se envían los datos, redirigir al formulario de login
            $_SESSION['success_message']="Por favor, ingresa tu DNI y contraseña.";
        }
    }
}

?>
