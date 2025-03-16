<?php
// Defino la clase Rutas para mejor funcionamiento
class RutasController
{
    public static function cargarVista()
    { // se crea el array de rutas para poder escalar el proyecto

        $mapeo = [
            //usuario - perfil
            "crear-usuario"     => "usuario/crear-usuario.php",
            "listado-usuarios"  => "usuario/listado-usuarios.php",
            "perfil-usuario"    => "usuario/perfil-usuario.php",
            "editar-usuario"    => "usuario/editar-usuario.php",
            //hombre-vivo
            "reporte"            => "h-vivo/registro.php",
            "listado_reportes"   => "h-vivo/listado_reportes.php",
            //Mensajes
            "bandeja-entrada"   => "mensajes/bandeja-entrada.php",
            "nuevo-mensaje"     => "mensajes/nuevo-mensaje.php",
            "mensajes-enviados" => "mensajes/mensajes-enviados.php",
            //objetivos
            "crear_objetivo"    => "objetivos/crear_objetivo.php",
            "editar_objetivo"   => "objetivos/editar_objetivo.php",
            "listado_objetivos" => "objetivos/listado_objetivos.php",
            //directivas
            "crear_directivas"    => "directivas/crear_directivas.php",
            "modificar_directivas"    => "directivas/modificar_directivas.php",
            "listado_directivas" => "directivas/listado_directivas.php",
            //rondas
            "crear_rondas"    => "rondas/crear_rondas.php",
            "generar_qr"      => "rondas/generar_qr.php",
            "imprimir_qr"     => "rondas/imprimir_qr.php",
            "editar_ronda"    => "rondas/editar_ronda.php",
            "listado_rondas"  => "rondas/listado_rondas.php",

            //web
            "login"             => "login.php",
            "forgot"            => "web/forgot-password.php"

        ];


        if (isset($_GET['r']) && array_key_exists($_GET['r'], $mapeo)) {
            $archivo = "vistas/paginas/" . $mapeo[$_GET['r']];
            include(file_exists($archivo) ? $archivo : "vistas/paginas/404.php");
        } else {
            include("vistas/paginas/inicio.php");
        }
    }
}
