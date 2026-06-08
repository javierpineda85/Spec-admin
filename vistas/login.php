<?php

// ② Procesar sólo si llegamos por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    LoginController::procesarLogin();
    // procesarLogin() usa header() + exit, así que no llega al include si redirige
}

// Ahora podemos incluir head.php y el resto del HTML
include_once 'contenido/head.php';
?>

<body class="content-wrapper container">
    <div class="row align-items-center justify-content-center mt-3">
        <div class="col-sm-12 col-md-6">
            <section class="content mt-2 mx-auto d-flex justify-content-center shadow p-3 mb-5 bg-white rounded">
                <div class="login-box">
                    <div class="login-logo">
                        <h2><b>SPEC</b> <br>Grupo Marsan S.A.</h2>
                        <img src="img/logo2024.png" width="100px">
                    </div>
                    <div class="card">
                        <div class="card-body login-card-body">
                            <h4 class="login-box-msg">Iniciar sesión</h4>

                            <!-- Mensaje de error/éxito -->
                            <?php if (!empty($_SESSION['success_message'])): ?>
                                <div class="alert alert-danger alert-dismissible mt-3">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <?= $_SESSION['success_message'] ?>
                                </div>
                                <?php unset($_SESSION['success_message']); ?>
                            <?php endif; ?>

                            <form action="?r=login" method="POST">
                                <div class="input-group mb-3">
                                    <input type="text" name="dni" class="form-control" placeholder="Ingresá tu DNI" required>
                                    <div class="input-group-append">
                                        <div class="input-group-text"><span class="far fa-id-card"></span></div>
                                    </div>
                                </div>
                                <div class="input-group mb-3">
                                    <input type="password" name="pass" class="form-control" placeholder="Ingresá tu contraseña" required>
                                    <div class="input-group-append">
                                        <div class="input-group-text"><span class="fas fa-lock"></span></div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
                                    </div>
                                </div>
                            </form>

                            <p class="my-2 text-center">
                                <a href="?r=reset-password">Olvidé la contraseña</a>
                            </p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <?php include_once 'contenido/scripts.php'; ?>
</body>

</html>