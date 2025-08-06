<?php
class NoticiasController
{
    public static function vistaCumple()
    {
        Auth::check('noticias', 'verCumples');

        $mesActual = date('m');
        $db = new Conexion;

        $cumples = $db->consultas("
            SELECT nombre, apellido, rol, f_nac 
            FROM usuarios 
            WHERE MONTH(f_nac) = ?
            ORDER BY DAY(f_nac)
        ", [$mesActual]);

        include 'vistas/paginas/admin/noticias/cumpleanos.php';
    }
}
