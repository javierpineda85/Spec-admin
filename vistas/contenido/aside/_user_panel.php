<div class="user-panel mt-3 pb-3 mb-3 d-flex">
  <div class="image">
    <img src="<?php echo $_SESSION['imgPerfil']; ?>" class="img-circle elevation-2" alt="imagen del usuario">
  </div>
  <div class="info">
    <a href="#" class="d-block"><?php echo $_SESSION['nombre'] . " " . $_SESSION['apellido']; ?></a>
  </div>
</div>