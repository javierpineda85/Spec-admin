<?php
class NoticiasController
{
    public static function vistaCumple()
    {
        Auth::check('noticias', 'verCumples');

        $mesActual = date('m');
        $db = new Conexion;
        $cumples = $db->consultas("SELECT u.nombre, u.apellido, r.nombre AS rol, u.f_nac
                                    FROM usuarios u
                                    JOIN roles r ON u.rol_id = r.id
                                    WHERE MONTH(u.f_nac) = ?
                                    ORDER BY DAY(u.f_nac)
                                ", [$mesActual]);


        include 'vistas/paginas/admin/noticias/cumpleanos.php';
    }
}
