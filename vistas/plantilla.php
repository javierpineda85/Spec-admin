<?php include_once('contenido/head.php'); ?>

<body class="hold-transition sidebar-mini sidebar-collapse">
  <style>
    .parent {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      grid-template-rows: repeat(3, 1fr);
      gap: 8px;
    }

    .parent1 {
      grid-row: span 3 / span 3;
    }


    .parent3 {
      grid-column-start: 2;
    }

    .parent4 {
      grid-column-start: 2;
      grid-row-start: 3;
    }
  </style>
  <!-- Site wrapper -->
  <div class="wrapper">

    <!-- Navbar -->

    <?php include_once('contenido/header.php'); ?>

    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <?php include_once('contenido/aside.php'); ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">


      <!-- Main content -->
      <section class="content mt-2">

        <!--AQUI VAN LAS VISTAS DEPENDIENDO DE LA RUTA -->
        <?php
        // Se instancia el objeto de ruta para cargar la vista correspondiente
        RutasController::cargarVista();
        ?>

      </section>
      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <footer class="main-footer">
      <?php include_once('contenido/footer.php'); ?>
    </footer>


    <!-- scripts -->
    <?php include_once('contenido/scripts.php'); ?>


</body>

</html>