<?php

// Reset password
if (isset($_GET['r']) && $_GET['r'] === 'reset-password') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        ResetPasswordController::crtResetPassword();
    } else {
        ResetPasswordController::vistaResetPassword();
    }
    return;
}

// Login
if (isset($_GET['r']) && $_GET['r'] === 'login') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        LoginController::procesarLogin();
    } else {
        LoginController::mostrarLogin();
    }
    return;
}