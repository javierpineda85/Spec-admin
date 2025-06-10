<?php
if (isset($_POST['idEliminar'])) {
    ControladorDirectivas::crtEliminarDirectiva();
}

?>

<!-- Main content -->
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3 class="card-title">Listado de directivas</h3>
                    </div>

                    <!-- /.card-header -->
                    <div class="card-body">
                        <?php if (!empty($_SESSION['success_message'])): ?>
                            <div class="alert alert-success alert-dismissible mt-3">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <i class="icon fas fa-check"></i>
                                <?= $_SESSION['success_message'];
                                unset($_SESSION['success_message']); ?>
                            </div>
                        <?php endif; ?>

                        <table id="example1" class="table table-bordered table-striped table-sm">
                            <thead>
                                <tr>
                                    <th style="text-align: center;" width="200px">Objetivo</th>
                                    <th style="text-align: center;">Detalle</th>
                                    <th style="text-align: center;" width="120px">Adjunto</th>
                                    <th style="text-align: center;" width="120px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($directivas as $valor) : ?>
                                    <tr>
                                        <!-- Objetivo -->
                                        <td style="vertical-align: middle; text-align: center;">
                                            <?php echo htmlspecialchars($valor['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                        </td>

                                        <!-- Detalle (con saltos de línea respetados) -->
                                        <td style="vertical-align: middle;">
                                            <?php echo nl2br(htmlspecialchars($valor['detalle'], ENT_QUOTES, 'UTF-8')); ?>
                                        </td>

                                        <!-- Adjunto: si existe ruta, se muestra enlace; si no, “—” -->
                                        <td style="vertical-align: middle; text-align: center;">
                                            <?php if (!empty($valor['adjunto'])): ?>
                                                <a href="<?php echo htmlspecialchars($valor['adjunto'], ENT_QUOTES, 'UTF-8'); ?>" 
                                                   target="_blank" 
                                                   class="btn btn-info btn-sm"
                                                   title="Ver adjunto">
                                                    <i class="fas fa-paperclip"></i>
                                                </a>
                                            <?php else: ?>
                                                &mdash;
                                            <?php endif; ?>
                                        </td>

                                        <!-- Acciones: Editar / Eliminar -->
                                        <td style="vertical-align: middle; text-align: center;">
                                            <div class="d-flex justify-content-center">
                                                <!-- Botón Editar -->
                                                <a href="?r=vistaEditarDirectiva&id=<?php echo $valor["idDirectiva"]; ?>" class="btn btn-success btn-sm mr-1" title="Editar directiva"><i class="fas fa-edit"></i>
                                                </a>

                                                <!-- Botón Eliminar (formulario) -->
                                                <form method="post" style="display:inline-block;">
                                                    <input type="hidden" name="idEliminar" value="<?php echo $valor["idDirectiva"]; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm"  title="Eliminar directiva" onclick="return confirm('¿Seguro que deseas eliminar PERMANENTEMENTE esta directiva?');"><i class="fas fa-trash-alt"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th style="text-align: center;">Objetivo</th>
                                    <th style="text-align: center;">Detalle</th>
                                    <th style="text-align: center;">Adjunto</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
    <!-- /.container-fluid -->

