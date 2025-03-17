<?php include_once('contenido/head.php'); ?>

<body class="content-wrapper container">

    <!-- Site wrapper -->
    <div class="row align-items-center justify-content-center mt-3" >
        <div class="col-sm-12 col-md-6">
            <!-- Main content -->
            <section class="content mt-2 mx-auto d-flex justify-content-center shadow p-3 mb-5 bg-white rounded">
                <div class="login-box">
                    <div class="login-logo">
                        <h2><b>S.P.E.C.</b> <br>Grupo Marsan S.A.</h2>
                        <img src="img/logo2024.png" alt="" width="100px">
                    </div>
                    <!-- /.login-logo -->
                    <div class="card">
                        <div class="card-body login-card-body">
                            <h4 class="login-box-msg">Iniciar sesión</h4>
                            <!-- Mostrar mensaje de error si no se ha podido autenticar -->
                            <?php
                            if (isset($error)) {
                                echo "<div class='alert alert-danger'>$error</div>";
                            }
                            ?>
                            <form action="" method="POST">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Ingresá tu DNI" name="dni">
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-envelope"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="input-group mb-3">
                                    <input type="password" class="form-control" placeholder="Ingresá tu contraseña" name="pass">
                                    <div class="input-group-append">
                                        <div class="input-group-text">
                                            <span class="fas fa-lock"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">

                                    <!-- /.col -->
                                    <div class="col-4">
                                        <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
                                        <?php $registro = LoginController::procesarLogin() ; ?>
                                    </div>
                                    <?php
                                        if (isset($_SESSION['success_message'])) {
                                        echo '<div class="alert alert-danger alert-dismissible mt-3">
                                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                                    <p><i class="icon fas fa-times"></i>' . $_SESSION['success_message'] .
                                            '</p></div>';
                                        // Elimina el mensaje después de mostrarlo
                                        unset($_SESSION['success_message']);
                                        };
                                        ?>
                                    <!-- /.col -->
                                </div>
                            </form>

                            <p class="my-2">
                                <a href="forgot-password.html">Olvidé la contraseña</a>
                            </p>

                        </div>
                        <!-- /.login-card-body -->
                    </div>
                </div>

            </section>

        </div>
    </div>

    <!-- scripts -->
    <?php include_once('contenido/scripts.php'); ?>

</body>

</html>
