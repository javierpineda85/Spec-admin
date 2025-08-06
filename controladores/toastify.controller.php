<?php
ob_start(); //permite enviar los headers sin interferencias


class ToastifyController
{
    public static function success($msg)
    {
        $_SESSION['toast'] = ['mensaje' => $msg, 'tipo' => 'success'];
    }
    public static function error($msg)
    {
        $_SESSION['toast'] = ['mensaje' => $msg, 'tipo' => 'danger'];
    }
    public static function warning($msg)
    {
        $_SESSION['toast'] = ['mensaje' => $msg, 'tipo' => 'warning'];
    }
    public static function info($msg)
    {
        $_SESSION['toast'] = ['mensaje' => $msg, 'tipo' => 'info'];
    }
}
