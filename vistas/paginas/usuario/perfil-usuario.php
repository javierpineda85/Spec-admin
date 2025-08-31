<?php
require_once "modelos/roles.modelo.php";

if (isset($_POST['modificar_usuario'])) {
    ControladorUsuarios::crtModificarUsuario();
}

// ID del usuario a editar
$idUsuario = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Cargar datos del usuario (con preparación para evitar inyección)
$pdo = Conexion::conectar();
$stmt = $pdo->prepare("
    SELECT u.*
    FROM usuarios u
    WHERE u.idUsuario = :id
    LIMIT 1
");
$stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Cargar roles activos para el selector
$roles = ModeloRoles::mdlObtenerRolesActivos();

// Nivel del usuario logueado (decimal 3,1). Ej: 1.0, 2.0, 3.0, 99.0
$nivelLogueado = isset($_SESSION['nivel']) ? floatval($_SESSION['nivel']) : 1.0;

// Helpers de bloqueo: niveles 1 y 2 solo pueden editar campos vacíos
function lockIfFilled(bool $isBlocked, $value): bool {
    return $isBlocked && strlen(trim((string)$value)) > 0;
}
$isBlockedLevel = in_array($nivelLogueado, [1.0, 2.0], true);

// Shortcuts de valores
$u = $usuario ?: [];
?>

<?php if (!$usuario): ?>
<div class="alert alert-danger">No se encontró el usuario solicitado.</div>
<?php return; endif; ?>

<div class="card">
    <div class="card card-info">
        <div class="card-header bg-info text-white">
            <h3 class="card-title">
                <?= htmlspecialchars(($u['nombre'] ?? '')) . " " . htmlspecialchars(($u['apellido'] ?? '')) ?>
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>

        <form class="form-horizontal" action="" method="POST" id="perfilForm" enctype="multipart/form-data">
            <div class="card-body row">

                <div class="col-sm-12 col-md-5">
                    <h5>Foto de Perfil</h5>
                    <div class="text-center">
                        <img class="profile-user-img img-fluid w-50"
                             src="<?= htmlspecialchars($u['imgPerfil'] ?? '') ?>"
                             alt="Foto de perfil"
                             style="cursor: default;">
                    </div>

                    <h5 class="mt-5">Foto Carnet Repriv</h5>
                    <div class="text-center">
                        <img src="<?= htmlspecialchars($u['imgRepriv'] ?? '') ?>"
                             alt="Carnet de REPRIV"
                             width="400"
                             style="cursor: default;">
                    </div>
                </div>

                <div class="col-sm-12 col-md-7">
                    <div class="row">
                        <div class="form-group col-sm-12 col-md-4">
                            <input type="hidden" name="idUsuario" value="<?= htmlspecialchars((string)$idUsuario) ?>">
                            <label class="form-label">Nombre</label>
                            <input
                                type="text"
                                class="form-control"
                                placeholder="Juan Carlos"
                                name="nombre"
                                value="<?= htmlspecialchars($u['nombre'] ?? '') ?>"
                                <?= lockIfFilled($isBlockedLevel, $u['nombre'] ?? '') ? 'readonly' : '' ?>
                                required
                            >
                        </div>

                        <div class="form-group col-sm-12 col-md-4">
                            <label class="form-label">Apellido</label>
                            <input
                                type="text"
                                class="form-control"
                                placeholder="Perez"
                                name="apellido"
                                value="<?= htmlspecialchars($u['apellido'] ?? '') ?>"
                                <?= lockIfFilled($isBlockedLevel, $u['apellido'] ?? '') ? 'readonly' : '' ?>
                                required
                            >
                        </div>

                        <div class="form-group col-sm-12 col-md-4">
                            <label class="form-label">DNI</label>
                            <input
                                type="text"
                                class="form-control"
                                placeholder="12345678"
                                name="dni"
                                id="inputDNI"
                                maxlength="8"
                                value="<?= htmlspecialchars($u['dni'] ?? '') ?>"
                                readonly
                                required
                            >
                            <small id="caracteresRestantes" class="form-text text-muted">Caracteres restantes: 8</small>
                        </div>

                        <div class="form-group col-sm-12 col-md-4">
                            <label class="form-label">Fecha Nac</label>
                            <input
                                type="date"
                                class="form-control"
                                name="f_nac"
                                value="<?= htmlspecialchars($u['f_nac'] ?? '') ?>"
                                <?= lockIfFilled($isBlockedLevel, $u['f_nac'] ?? '') ? 'readonly' : '' ?>
                                required
                            >
                        </div>

                        <div class="form-group col-sm-12 col-md-4">
                            <label class="form-label">Rol de Usuario</label>
                            <?php
                                $lockRol = lockIfFilled($isBlockedLevel, $u['rol_id'] ?? '');
                            ?>
                            <select
                                class="custom-select"
                                name="rol"
                                <?= $lockRol ? 'disabled' : '' ?>
                                required >
                                <?php foreach ($roles as $rol): ?>
                                    <option
                                        value="<?= (int)$rol['id'] ?>"
                                        <?= ((string)($u['rol_id'] ?? '') === (string)$rol['id']) ? 'selected' : '' ?> >
                                        <?= htmlspecialchars($rol['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($lockRol): ?>
                                <!-- Preserva el valor al enviar si el select está deshabilitado -->
                                <input type="hidden" name="rol" value="<?= htmlspecialchars((string)($u['rol_id'] ?? '')) ?>">
                            <?php endif; ?>
                        </div>

                        <div class="form-group col-sm-12 col-md-4">
                            <label class="form-label">Teléfono</label>
                            <input
                                type="text"
                                class="form-control"
                                placeholder="2612223333"
                                name="telefono"
                                value="<?= htmlspecialchars($u['telefono'] ?? '') ?>"
                                <?= lockIfFilled($isBlockedLevel, $u['telefono'] ?? '') ? 'readonly' : '' ?>
                                required
                            >
                        </div>

                        <div class="form-group col-sm-12 col-md-4">
                            <label class="form-label">Teléfono de Emergencia</label>
                            <input
                                type="text"
                                class="form-control"
                                placeholder="2612223333"
                                name="tel_emergencia"
                                value="<?= htmlspecialchars($u['tel_emergencia'] ?? '') ?>"
                                <?= lockIfFilled($isBlockedLevel, $u['tel_emergencia'] ?? '') ? 'readonly' : '' ?>
                            >
                        </div>

                        <div class="form-group col-sm-12 col-md-4">
                            <label class="form-label">Nombre de Contacto</label>
                            <input
                                type="text"
                                class="form-control"
                                placeholder="Juan Perez"
                                name="nombre_contacto"
                                value="<?= htmlspecialchars($u['nombre_contacto'] ?? '') ?>"
                                <?= lockIfFilled($isBlockedLevel, $u['nombre_contacto'] ?? '') ? 'readonly' : '' ?>
                            >
                        </div>

                        <div class="form-group col-sm-12 col-md-4">
                            <label class="form-label">Parentesco</label>
                            <input
                                type="text"
                                class="form-control"
                                placeholder="Parentesco"
                                name="parentesco"
                                value="<?= htmlspecialchars($u['parentesco'] ?? '') ?>"
                                <?= lockIfFilled($isBlockedLevel, $u['parentesco'] ?? '') ? 'readonly' : '' ?>
                            >
                        </div>

                        <div class="form-group col-sm-12 col-md-5">
                            <label class="form-label">Domicilio</label>
                            <input
                                type="text"
                                class="form-control"
                                placeholder="Av San Martin 123 Ciudad"
                                name="domicilio"
                                value="<?= htmlspecialchars($u['domicilio'] ?? '') ?>"
                                <?= lockIfFilled($isBlockedLevel, $u['domicilio'] ?? '') ? 'readonly' : '' ?>
                                required
                            >
                        </div>

                        <div class="form-group col-sm-12 col-md-5">
                            <label class="form-label">Provincia</label>
                            <?php
                                $lockProv = lockIfFilled($isBlockedLevel, $u['provincia'] ?? '');
                                $provValue = htmlspecialchars($u['provincia'] ?? '');
                            ?>
                            <select
                                id="provincia"
                                name="provincia"
                                class="form-control"
                                <?= $lockProv ? 'disabled' : '' ?>
                                required
                            >
                                <?php if ($provValue !== ''): ?>
                                    <option value="<?= $provValue ?>" selected><?= $provValue ?></option>
                                <?php else: ?>
                                    <option value="" disabled selected>Elige una provincia</option>
                                <?php endif; ?>
                                <!-- Si se carga dinámicamente, insertar aquí -->
                            </select>
                            <?php if ($lockProv): ?>
                                <input type="hidden" name="provincia" value="<?= $provValue ?>">
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <div class="row border-top-secundary">
                        <div class="form-group col-md-6">
                            <label for="inputGroupFile01">Cambiar Foto de Perfil</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input
                                        type="hidden"
                                        name="imgPerfilActual"
                                        value="<?= htmlspecialchars($u['imgPerfil'] ?? '') ?>"
                                    >
                                    <input
                                        type="file"
                                        class="custom-file-input"
                                        id="inputGroupFile01"
                                        name="imgPerfil"
                                        accept=".png,.jpg,.jpeg"
                                        <?= lockIfFilled($isBlockedLevel, $u['imgPerfil'] ?? '') ? 'disabled' : '' ?>
                                    >
                                    <label class="custom-file-label" for="inputGroupFile01">Selecciona un archivo</label>
                                </div>
                            </div>
                            <small class="form-text text-muted">Solo formatos .png, .jpg o .jpeg</small>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="inputGroupFile02">Cambiar Carnet de REPRIV</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input
                                        type="hidden"
                                        name="imgReprivActual"
                                        value="<?= htmlspecialchars($u['imgRepriv'] ?? '') ?>"
                                    >
                                    <input
                                        type="file"
                                        class="custom-file-input"
                                        id="inputGroupFile02"
                                        name="imgRepriv"
                                        accept=".png,.jpg,.jpeg"
                                        <?= lockIfFilled($isBlockedLevel, $u['imgRepriv'] ?? '') ? 'disabled' : '' ?>
                                    >
                                    <label class="custom-file-label" for="inputGroupFile02">Selecciona un archivo</label>
                                </div>
                            </div>
                            <small class="form-text text-muted">Solo formatos .png, .jpg o .jpeg</small>
                        </div>
                    </div>

                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'Gerencia'): ?>
                        <div class="form-group col-sm-12 col-md-12 p-3 border border-secondary">
                            <label class="form-label">Estado y seguridad</label>

                            <div class="custom-control custom-switch my-1">
                                <input type="checkbox" name="resetPass" class="custom-control-input switch-warning" id="customSwitch1">
                                <label class="custom-control-label text-secondary" for="customSwitch1">Restaurar contraseña</label>
                                <small class="form-text text-muted">
                                    Al restaurar la contraseña, el usuario deberá escribir una nueva contraseña para iniciar sesión
                                </small>
                            </div>

                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="activo" class="custom-control-input switch-danger" id="customSwitch2">
                                <label class="custom-control-label text-secondary" for="customSwitch2">Dar de baja</label>
                            </div>

                            <div id="motivoContainer" style="display: none; margin-top: 10px;">
                                <input type="text" name="motivo" class="form-control" placeholder="Por favor indique el motivo">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-footer">
                <input type="submit" class="btn btn-success" value="Modificar datos" name="modificar_usuario">
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const checkbox = document.getElementById("customSwitch2");
    const motivoContainer = document.getElementById("motivoContainer");
    if (checkbox && motivoContainer) {
        checkbox.addEventListener("change", function() {
            motivoContainer.style.display = this.checked ? "block" : "none";
        });
    }
});
</script>