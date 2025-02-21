<?php
    // Defino la clase Rutas para mejor funcionamiento
    class RutasController {
        public static function cargarVista(){ // se crea el array de rutas para poder escalar el proyecto

         $mapeo = [
            //usuario - perfil
            "crear-usuario"     => "usuario/crear-usuario.php",
            "listado-usuarios"  => "usuario/listado-usuarios.php",
            "perfil-usuario"    => "usuario/perfil-usuario.php",
            "editar-usuario"    => "usuario/editar-usuario.php",
            //hombre-vivo
            "h-vivo"            => "h-vivo/registro.php",
            //Mensajes
            "bandeja-entrada"   => "mensajes/bandeja-entrada.php",
            "nuevo-mensaje"     => "mensajes/nuevo-mensaje.php",
            "mensajes-enviados" => "mensajes/mensajes-enviados.php",
            //web
            "login"             =>"web/login.php",
            "forgot"            =>"web/forgot-password.php"

        ];


        if (isset($_GET['r']) && array_key_exists($_GET['r'], $mapeo)) {
            $archivo = "vistas/paginas/" . $mapeo[$_GET['r']];
            include(file_exists($archivo) ? $archivo : "vistas/paginas/404.php");
        } else {
            include("vistas/paginas/inicio.php");
        }
    }
}