<?php
// core/Auth.php

/**
 * Clase de autenticación y autorización centralizada.
 *
 * Uso:
 *   Auth::requireRole(['Supervisor2','Administrativo']);
 *   Auth::requireRondaAsignada();
 */
class Auth
{
    /**
     * Verifica que el usuario tenga uno de los roles permitidos.
     * Si no, muestra 403 y detiene la ejecución.
     *
     * @param array $roles Lista de roles autorizados.
     */
    public static function requireRole(array $roles)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userRole = $_SESSION['rol'] ?? null;
        if (!$userRole || !in_array($userRole, $roles)) {
            http_response_code(403);
            include __DIR__ . '/../vistas/paginas/403.php';
            exit;
        }
    }

    /**
     * Verifica que el usuario tenga asignada una ronda (solo Vigilador/Referente).
     * Si no, redirige con mensaje.
     */
    public static function requireRondaAsignada()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $rondaId = $_SESSION['ronda_id'] ?? 0;
        $rol         = $_SESSION['rol'] ?? '';
        $isReferente = $_SESSION['isReferente'] ?? false;
        // Solo Vigilador o quien tenga isReferente = true
        if ($rol === 'Vigilador' || $isReferente) {
            $rondaId = $_SESSION['ronda_id'] ?? 0;
            if (!$rondaId) {
                $_SESSION['error_message'] = 'No tienes ronda asignada hoy.';
                header('Location: index.php');
                exit;
            }
        }
    }

    /**
     * Redirige a la página de login si no hay usuario autenticado.
     */
    public static function requireLogin()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['idUsuario'])) {
            header('Location: index.php?r=login');
            exit;
        }
    }
}
