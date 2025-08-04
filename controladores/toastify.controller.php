<?php
ob_start();

class ToastifyController
{
    public static function success($msg)
    {
        $_SESSION['toastify'][] = ['mensaje' => $msg, 'color' => '#28a745']; // verde
    }

    public static function error($msg)
    {
        $_SESSION['toastify'][] = ['mensaje' => $msg, 'color' => '#dc3545']; // rojo
    }

    public static function warning($msg)
    {
        $_SESSION['toastify'][] = ['mensaje' => $msg, 'color' => '#ffc107']; // amarillo
    }

    public static function info($msg)
    {
        $_SESSION['toastify'][] = ['mensaje' => $msg, 'color' => '#17a2b8']; // azul info
    }

    public static function render()
    {
        if (!empty($_SESSION['toastify'])) {
            echo "<script>";
            foreach ($_SESSION['toastify'] as $toast) {
                echo "Toastify({
                    text: '" . addslashes($toast['mensaje']) . "',
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '" . $toast['color'] . "',
                    close: true
                }).showToast();";
            }
            echo "</script>";
            unset($_SESSION['toastify']);
        }
    }
}
