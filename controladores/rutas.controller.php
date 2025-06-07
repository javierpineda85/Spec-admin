<?php
// Defino la clase Rutas para mejor funcionamiento
class RutasController
{
    public static function cargarVista()
    {
        //Para usar controladores sin pasar la vista
        if (isset($_GET['r']) && $_GET['r'] === 'buscar_cronogramas') {
            ControladorTurnos::crtBuscarTurnosPorRango();
            return; // Importante: salimos para no caer en el include de más abajo
        }
        if (isset($_GET['r']) && $_GET['r'] === 'buscar_porVigilador') {
            ControladorTurnos::crtBuscarPorVigilador();
            return;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'buscar_resumen_diario') {
            ControladorCronograma::crtBuscarResumenDiario();
            return;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'buscar_resumen_horas') {
            ControladorCronograma::crtBuscarResumenHoras();
            return;
        }
        if (isset($_GET['r']) && $_GET['r'] === 'buscar_resumen_horas_por_vigilador') {
            ControladorCronograma::crtBuscarResumenHorasPorVigilador();
            return;
        }


        // se crea el array de rutas para poder escalar el proyecto
        $mapeo = [
            //usuario - perfil
            "crear-usuario"     => "usuario/crear-usuario.php",
            "listado-usuarios"  => "usuario/listado-usuarios.php",
            "listado-usuarios-inactivos"  => "usuario/listado-usuarios-inactivos.php",
            "perfil-usuario"    => "usuario/perfil-usuario.php",
            "cerrar_sesion"     => "usuario/salir.php",
            //hombre-vivo
            "reporte"            => "h-vivo/registro.php",
            "listado_reportes"   => "h-vivo/listado_reportesHvivo.php",
            //Mensajes
            "bandeja-entrada"   => "mensajes/bandeja-entrada.php",
            "nuevo-mensaje"     => "mensajes/nuevo-mensaje.php",
            "mensajes-enviados" => "mensajes/mensajes-enviados.php",
            //objetivos
            "crear_objetivo"    => "objetivos/crear_objetivo.php",
            "editar_objetivo"   => "objetivos/editar_objetivo.php",
            "listado_objetivos" => "objetivos/listado_objetivos.php",
            "listado_objetivos_inactivos" => "objetivos/listado_objetivos_desactivados.php",
            //directivas
            "crear_directivas"        => "directivas/crear_directivas.php",
            "modificar_directivas"    => "directivas/modificar_directivas.php",
            "listado_directivas"      => "directivas/listado_directivas.php",
            //rondas
            "crear_rondas"    => "rondas/crear_rondas.php",
            "generar_qr"      => "rondas/generar_qr.php",
            "imprimir_qr"     => "rondas/imprimir_qr.php",
            "editar_ronda"    => "rondas/editar_ronda.php",
            "listado_rondas"  => "rondas/listado_rondas.php",
            //Cronograma
            "crear_cronograma"      => "cronogramas/crear_cronograma.php",
            "listado_cronogramas"   => "cronogramas/listado_cronogramas.php",
            "listado_porVigilador"  => "cronogramas/listado_porVigilador.php",
            "listado_resumen_diario" => "cronogramas/resumen_diario_jornadas.php",
            "reporte_porHoras"      => "cronogramas/reporte_horas_por_objetivo.php",
            "reporte_porVigilador"  => "cronogramas/reporte_horas_vigilador.php",

            //Novedades
            "entradas_salidas"      => "novedades/entradas_salidas.php",

            //Puestos
            "crear_puesto"      => "puestos/crear_puesto.php",
            "listado_puestos"   => "puestos/listado_puestos.php",
            "editar_puesto"     => "puestos/editar_puesto.php",
            "listado_puestos_inactivos"     => "puestos/listado_puestos_desactivados.php",

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
