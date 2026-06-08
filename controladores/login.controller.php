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

    public static function mostrarLogin()
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

                // Forzar cambio de contraseña si resetPass = 0
                if ($user['resetPass'] === 0) {
                    $_SESSION['force_reset_id'] = $user['idUsuario'];
                    header('Location: index.php?r=reset-password');
                    exit;
                }
                // Datos básicos
                $_SESSION['idUsuario']  = $user['idUsuario'];
                $_SESSION['nombre']     = $user['nombre'];
                $_SESSION['apellido']   = $user['apellido'];
                $_SESSION['imgPerfil']  = $user['imgPerfil'];

                // Datos de rol
                $_SESSION['rol_id']     = $user['rol_id'];
                $_SESSION['rol']        = $user['nombreRol']; // solo para mostrar
                $_SESSION['nivel']      = $user['nivel'];
                $_SESSION['categoria']  = $user['categoria'];
                $_SESSION['reservado']  = $user['reservado'];

                // Si es vigilador o referente, cargar asignación del día
                if (in_array($_SESSION['categoria'], ['operativo', 'referente'])) {
                    $asig = $modeloUsuarios->getAsignacionHoy($_SESSION['idUsuario']);
                    if ($asig) {
                        $_SESSION['puesto_id']    = $asig['puesto_id'];
                        $_SESSION['objetivo_id']  = $asig['objetivo_id'];
                        $_SESSION['isReferente']  = !empty($asig['is_referente']);
                        unset($_SESSION['sinAsignaciones']);
                    } else {
                        $_SESSION['puesto_id']    = 0;
                        $_SESSION['objetivo_id']  = 0;
                        $_SESSION['isReferente']  = false;
                        $_SESSION['sinAsignaciones'] = true;
                    }
                }

                // Cargar permisos del rol (una sola vez en login)
                unset($_SESSION['permisos_usuario']);

                if ($_SESSION['rol'] === 'Programador') {
                    // BYPASS total para Programador
                    $_SESSION['permisos_usuario'] = ['*'];
                } else {
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
                }

                // Alias si lo usás en otros lugares
                $_SESSION['permisos'] = $_SESSION['permisos_usuario'];

                // Redirigir al inicio
                header('Location: index.php');
                exit();
            } else {
                $_SESSION['success_message'] = "DNI o contraseña incorrectos.";
                header('Location: index.php?r=login');
            }
        } else {
            //$_SESSION['success_message'] = "Por favor, ingresa tu DNI y contraseña.";
        }
    }
}
