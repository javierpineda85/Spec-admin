<?php
include_once 'contenido/head.php';

$forced = !empty($_SESSION['force_reset_id']);
?>

<body class="content-wrapper container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <section class="shadow p-4 bg-white rounded">
                <h4 class="text-center">
                    <?= $forced
                        ? 'Debes restablecer tu contraseña'
                        : 'Olvidé mi contraseña'
                    ?>
                </h4>

                <!-- Mostrar errores -->
                <?php if (!empty($_SESSION['reset_error'])): ?>
                    <div class="alert alert-danger">
                        <?= $_SESSION['reset_error'] ?>
                    </div>
                    <?php unset($_SESSION['reset_error']); ?>
                <?php endif; ?>

                <form action="index.php?r=reset-password" method="POST">
                    <?php if (! $forced): ?>
                        <!-- Campo DNI solo en forgot-password -->
                        <div class="form-group">
                            <label for="dni">Tu DNI</label>
                            <input type="text"
                                name="dni"
                                class="form-control"
                                placeholder="Ingresá tu DNI"
                                required>
                        </div>
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="new_pass">Nueva contraseña</label>
                        <input type="password"
                            name="new_pass"
                            class="form-control"
                            required>
                    </div>

                    <div class="form-group">
                        <label for="new_pass_confirm">Confirmar contraseña</label>
                        <input type="password"
                            name="new_pass_confirm"
                            class="form-control"
                            required>
                    </div>

                    <button class="btn btn-primary btn-block">
                        <?= $forced ? 'Actualizar contraseña' : 'Restablecer contraseña' ?>
                    </button>
                </form>

                <p class="mt-3 text-center">
                    <a href="index.php?r=login">Volver al login</a>
                </p>
            </section>
        </div>
    </div>

    <?php include_once 'contenido/scripts.php'; ?>
</body>

</html>