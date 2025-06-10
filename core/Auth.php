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
    /**
     * Lanza acceso denegado si el usuario no tiene permiso.
     * @param string $controller  nombre en minúsculas (p.ej. 'novedades')
     * @param string $action      nombre exacto del método (p.ej. 'crtListarNovedades')
     */
    public static function check(string $controller, string $action)
    {
        // arrancar sesión si no está
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        // usa tu middleware genérico bajo el capó
        CheckPermissionMiddleware::handle($controller, $action);
    }
public static function hasPermission(string $controller, string $action): bool
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Si aún no hemos precargado los permisos en sesión, los cargamos ahora
    if (!isset($_SESSION['permisos_usuario'])) {
        $rol = $_SESSION['rol'] ?? '';
        if (!$rol) {
            // Sin rol, sin permisos
            $_SESSION['permisos_usuario'] = [];
        } else {
            $db = new Conexion();
            $res = $db->consultas(
                "SELECT p.controlador, p.accion
                   FROM role_permissions rp
                   JOIN permissions p ON rp.permission_id = p.id
                  WHERE rp.role = ?",
                [$rol]
            );
            // Formateamos como “controlador/accion”
            $_SESSION['permisos_usuario'] = array_map(
                fn($r) => "{$r['controlador']}/{$r['accion']}",
                $res
            );
        }
    }

    // Y comprobamos si existe en el array precargado
    return in_array("$controller/$action", $_SESSION['permisos_usuario'], true);
}

}
