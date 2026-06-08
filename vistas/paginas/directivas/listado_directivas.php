<?php
if (isset($_POST['idEliminar'])) {
    ControladorDirectivas::crtEliminarDirectiva();
}

// Filtro por tipo (opcional)
$tipoFiltro = $_GET['tipo'] ?? '';
?>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Listado de directivas</h3>
                        <!-- Filtro por tipo -->
                        <form method="get" class="form-inline">
                            <input type="hidden" name="r" value="listado_directivas">
                            <select name="tipo" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                                <option value="">-- Todas --</option>
                                <option value="general" <?= $tipoFiltro === 'general' ? 'selected' : '' ?>>Generales</option>
                                <option value="particular" <?= $tipoFiltro === 'particular' ? 'selected' : '' ?>>Particulares</option>
                                <option value="eventual" <?= $tipoFiltro === 'eventual' ? 'selected' : '' ?>>Eventuales</option>
                            </select>
                        </form>
                    </div>

                    <div class="card-body">

                        <table id="example1" class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th style="text-align: center;" width="150px">Objetivo</th>
                                    <th style="text-align: center;" width="100px">Tipo</th>
                                    <th style="text-align: center;">Detalle</th>
                                    <th style="text-align: center;" width="80px">Adjunto</th>
                                    <?php if ($_SESSION['nivel'] > 2 && !in_array($_SESSION['categoria'] ?? '', ['operativo', 'referente'], true)): ?>
                                        <th style="text-align: center;" width="120px">Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($directivas as $valor) :
                                    // Si hay filtro, saltar las que no coincidan
                                    if ($tipoFiltro && $valor['tipo'] !== $tipoFiltro) continue;

                                    // Badge por tipo
                                    $badgeClass = [
                                        'general'    => 'badge-primary',
                                        'particular' => 'badge-success',
                                        'eventual'   => 'badge-warning'
                                    ][$valor['tipo']] ?? 'badge-secondary';
                                ?>
                                    <tr>
                                        <!-- Objetivo -->
                                        <td style="vertical-align: middle; text-align: center;">
                                            <?= htmlspecialchars($valor['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <!-- Tipo -->
                                        <td style="vertical-align: middle; text-align: center;">
                                            <span class="badge <?= $badgeClass ?>">
                                                <?= ucfirst(htmlspecialchars($valor['tipo'], ENT_QUOTES, 'UTF-8')) ?>
                                            </span>
                                        </td>

                                        <!-- Detalle -->
                                        <td style="vertical-align: middle;">
                                            <?= nl2br(htmlspecialchars($valor['detalle'], ENT_QUOTES, 'UTF-8')); ?>
                                        </td>

                                        <!-- Adjunto -->
                                        <td style="vertical-align: middle; text-align: center;">
                                            <?php if (!empty($valor['adjunto'])): ?>
                                                <a href="<?= htmlspecialchars($valor['adjunto'], ENT_QUOTES, 'UTF-8'); ?>"
                                                    target="_blank"
                                                    class="btn btn-info btn-sm"
                                                    title="Ver adjunto">
                                                    <i class="fas fa-paperclip"></i>
                                                </a>
                                            <?php else: ?>
                                                &mdash;
                                            <?php endif; ?>
                                        </td>

                                        <!-- Acciones -->
                                        <?php if ($_SESSION['nivel'] > 2 && !in_array($_SESSION['categoria'] ?? '', ['operativo', 'referente'], true)): ?>
                                            <td style="vertical-align: middle; text-align: center;">
                                                <div class="d-flex justify-content-center">
                                                    <!-- Editar -->
                                                    <a href="?r=vistaEditarDirectiva&id=<?= $valor["idDirectiva"]; ?>"
                                                        class="btn btn-success btn-sm mr-1"
                                                        title="Editar directiva">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    <!-- Eliminar (solo si no es general) -->
                                                    <?php if ($valor['tipo'] !== 'general'): ?>
                                                        <form method="post" style="display:inline-block;">
                                                            <input type="hidden" name="idEliminar" value="<?= $valor["idDirectiva"]; ?>">
                                                            <button type="submit"
                                                                class="btn btn-danger btn-sm"
                                                                title="Eliminar directiva"
                                                                onclick="return confirm('¿Seguro que deseas eliminar PERMANENTEMENTE esta directiva?');">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th style="text-align: center;">Objetivo</th>
                                    <th style="text-align: center;">Tipo</th>
                                    <th style="text-align: center;">Detalle</th>
                                    <th style="text-align: center;">Adjunto</th>
                                    <?php if ($_SESSION['nivel'] > 2 && !in_array($_SESSION['categoria'] ?? '', ['operativo', 'referente'], true)): ?>
                                        <th style="text-align: center;">Acciones</th>
                                    <?php endif; ?>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
