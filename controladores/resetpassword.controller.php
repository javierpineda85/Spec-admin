<?php
require_once 'modelos/usuarios.modelo.php';

class ResetPasswordController
{
    /**
     * Mostrar formulario de reset.
     * Si vengo de “olvidé contraseña” muestro campo DNI.
     * Si vengo de “forzar reset” muestro solo nueva contraseña.
     */
    public static function vistaResetPassword()
    {
        // ① Arrancar sesión
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // ② ¿Es forced reset? (llegué tras login con resetPass=0)
        $forced = !empty($_SESSION['force_reset_id']);

        // Incluir la vista y pasarle la variable $forced
        include __DIR__ . '/../vistas/reset-password.php';
    }

    /**
     * Procesar el POST del formulario de reset.
     * Si $forced==true, uso el ID en sesión.
     * Si $forced==false, busco el usuario por DNI del form.
     */
    public static function crtResetPassword()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Recibimos campos
        $newPass = $_POST['new_pass']         ?? '';
        $confirm = $_POST['new_pass_confirm'] ?? '';
        $dni     = $_POST['dni']             ?? null;

        // ① Validación: contraseñas iguales y no vacías
        if (empty($newPass) || $newPass !== $confirm) {
            $_SESSION['reset_error'] = 'Las contraseñas no coinciden o están vacías.';
            header('Location: index.php?r=reset-password');
            exit;
        }

        // ② Determinar el user ID
        if (!empty($_SESSION['force_reset_id'])) {
            // Forced reset tras login
            $uid = $_SESSION['force_reset_id'];
        } else {
            // Forgot password: buscar por DNI
            $modelo = new ModeloUsuarios();
            $u = $modelo->getUsuarioPorDni($dni);
            if (!$u) {
                $_SESSION['reset_error'] = 'DNI no registrado.';
                header('Location: index.php?r=reset-password');
                exit;
            }
            $uid = $u[0]['idUsuario'];
        }

        // ③ Hashear y guardar nueva contraseña
        $hash = password_hash($newPass, PASSWORD_DEFAULT);
        $res  = ModeloUsuarios::mdlActualizarPassReset($uid, $hash);

        if ($res === 'ok') {
            // Si era forced: limpio el flag
            unset($_SESSION['force_reset_id']);

            // Mensaje de éxito y redirijo al login
            $_SESSION['success_message'] = 'Contraseña actualizada. Por favor ingresa.';
            header('Location: index.php?r=login');
            exit;
        }

        // ④ Error al guardar en BD
        $_SESSION['reset_error'] = 'Hubo un problema al actualizar la contraseña.';
        header('Location: index.php?r=reset-password');
        exit;
    }
}
