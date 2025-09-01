<?php if (!empty($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="icon fas fa-check"></i>
        <?= $_SESSION['success_message'];
        unset($_SESSION['success_message']); ?>
    </div>
<?php endif; ?>

<?php if (!empty($_SESSION['success_error'])): ?>
    <div class="alert alert-danger alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-exclamation-triangle"></i>
        <?= $_SESSION['success_error'];
        unset($_SESSION['success_error']); ?>
    </div>
<?php endif; ?>

<?php if (!empty($_SESSION['sinAsignaciones'])): ?>
    <div class="alert alert-success text-center mt-3">
        <strong>Bienvenido.</strong> Aún no tienes objetivos asignados.
    </div>
    <?php unset($_SESSION['sinAsignaciones']); ?>
<?php endif; ?>