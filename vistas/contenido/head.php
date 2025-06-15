<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="author" content="Javier Pineda">
  <meta name="description" content="Sistema de gestión de trabajo de la empresa de seguridad S.P.E.C. perteneciente al grupo MARSAN S.A.">
  <link rel="shortcut icon" href="img/spec-favicon.ico" type="image/x-icon">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">

  <title>S.P.E.C. </title>

  <link rel="manifest" href="<?= BASE_URL ?>/manifest.json">
  <meta name="theme-color" content="#007bff">
  <!-- Registro del Service Worker -->
  <script src="<?= BASE_URL ?>/sw-register.js"></script>

  <!-- Audio para alertas -->
  <audio id="sonido-alerta-global" src="<?= BASE_URL ?>/public/sonidos/spec_notificacion.mp3" preload="auto"></audio>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="./css/all.min.css">
  <script src="https://kit.fontawesome.com/7907a05fb3.js"></script>
  <!-- Theme style -->
  <link rel="stylesheet" href="./css/adminlte.min.css">

  <!-- Estilos propios -->
  <link rel="stylesheet" href="./css/spec.css">

  <!-- Bootstrap4 Duallistbox -->
  <link rel="stylesheet" href="./plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css">
  <link rel="stylesheet" href="./plugins/bootstrap4-duallistbox/bootstrap-duallistbox.css">
  <!-- BS Stepper -->
  <link rel="stylesheet" href="./plugins/bs-stepper/css/bs-stepper.min.css">
  <!-- BS switch -->
  <link rel="stylesheet" href="./plugins/bootstrap-switch/css/bootstrap3/bootstrap-switch.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="./css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="./css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="./css/buttons.bootstrap4.min.css">

  <!-- Toastify -->
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.css">
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="./js/main.js"></script>

  <!--Para crear objetos buscables-->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />

  <!--Para Geoposicion-->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
</head>