<?php

// Buscar cronogramas
if (isset($_GET['r']) && $_GET['r'] === 'buscar_cronogramas') {
    ControladorTurnos::crtBuscarTurnosPorRango();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'buscar_porVigilador') {
    ControladorTurnos::crtBuscarPorVigilador();
    return;
}

// Crear cronograma
if (isset($_GET['r']) && $_GET['r'] === 'crear_cronograma') {
    ControladorCronogramas::vistaCrearCronograma();
    return;
}

// Listado cronogramas
if (isset($_GET['r']) && $_GET['r'] === 'listado_cronogramas') {
    ControladorCronogramas::vistaListadoCronogramas();
    return;
}

// Listado cronogramas por vigilador
if (isset($_GET['r']) && $_GET['r'] === 'listado_porVigilador') {
    ControladorCronogramas::vistaListadoCronogramaPorVigilador();
    return;
}

// Jornadas por objetivo
if (isset($_GET['r']) && $_GET['r'] === 'listado_resumen_diario') {
    ControladorCronogramas::vistaJornadasPorObjetivo();
    return;
}

// Horas por vigilador
if (isset($_GET['r']) && $_GET['r'] === 'reporte_porVigilador') {
    ControladorCronogramas::vistaHorasPorVigilador();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'buscar_resumen_diario') {
    ControladorCronogramas::crtBuscarResumenDiario();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'buscar_resumen_horas') {
    ControladorCronogramas::crtBuscarResumenHoras();
    return;
}

if (isset($_GET['r']) && $_GET['r'] === 'buscar_resumen_horas_por_vigilador') {
    $desde = $_POST['desde'] ?? null;
    $hasta = $_POST['hasta'] ?? null;
    ControladorCronogramas::crtBuscarResumenHorasPorVigilador($desde, $hasta);
    return;
}

// Procesar POST de horas por objetivo
if (isset($_GET['r']) && $_GET['r'] === 'reporte_porHoras' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    ControladorCronogramas::crtBuscarResumenHoras();
    return;
}

// Vista reporte horas por objetivo
if (isset($_GET['r']) && $_GET['r'] === 'reporte_porHoras') {
    ControladorCronogramas::vistaReporteHorasPorObjetivo();
    return;
}