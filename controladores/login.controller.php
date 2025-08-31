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
        // Auth::check('login', 'mostrarLogin');
        include_once('vistas/login.php');
    }

    public static function procesarLogin()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_POST['dni']) && isset($_POST['pass'])) {
            $dni      = trim($_POST['dni']);
            $password = $_POST['pass'];

            $modeloUsuarios = new ModeloUsuarios();
            $esAutenticado  = $modeloUsuarios->authenticate($dni, $password);

            if ($esAutenticado) {
                $user = $esAutenticado[0];

                // Datos básicos
                $_SESSION['idUsuario']  = (int)$user['idUsuario'];
                $_SESSION['nombre']     = $user['nombre'];
                $_SESSION['apellido']   = $user['apellido'];
                $_SESSION['imgPerfil']  = $user['imgPerfil'];

                // Datos de rol
                $_SESSION['rol_id']     = (int)$user['rol_id'];
                $_SESSION['rol']        = $user['nombreRol']; // solo para mostrar
                $_SESSION['nivel']      = (int)$user['nivel'];
                $_SESSION['categoria']  = $user['categoria'];
                $_SESSION['reservado']  = (int)$user['reservado'];

                unset($_SESSION['permisos_usuario']);

                // Si es vigilador o referente, cargar asignación del día
                if (in_array($_SESSION['categoria'], ['operativo', 'referente'])) {
                    $asig = $modeloUsuarios->getAsignacionHoy($_SESSION['idUsuario']);
                    if ($asig) {
                        $_SESSION['puesto_id']    = (int)$asig['puesto_id'];
                        $_SESSION['objetivo_id']  = (int)$asig['objetivo_id'];
                        $_SESSION['isReferente']  = !empty($asig['is_referente']);
                        unset($_SESSION['sinAsignaciones']);
                    } else {
                        $_SESSION['puesto_id']    = 0;
                        $_SESSION['objetivo_id']  = 0;
                        $_SESSION['isReferente']  = false;
                        $_SESSION['sinAsignaciones'] = true;
                    }
                }

                // Cargar permisos del rol
                $db = new Conexion();
                $resultados = $db->consultas(
                    "SELECT p.controlador, p.accion
                 FROM role_permissions rp
                 JOIN permissions p ON rp.permission_id = p.id
                 WHERE rp.role_id = ?",
                    [$_SESSION['rol_id']]
                );

                $_SESSION['permisos_usuario'] = array_map(
                    fn($r) => "{$r['controlador']}/{$r['accion']}",
                    $resultados
                );
                $_SESSION['permisos'] = $_SESSION['permisos_usuario'];

                // Redirigir al inicio
                header('Location: index.php');
                exit();
            } else {
                $_SESSION['success_message'] = "DNI o contraseña incorrectos.";
            }
        } else {
            $_SESSION['success_message'] = "Por favor, ingresa tu DNI y contraseña.";
        }
    }
}
