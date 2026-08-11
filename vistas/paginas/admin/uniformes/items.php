<?php
$esc = static function ($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
};
?>

<div class="container-fluid px-2">
<div class="row">
    <div class="col-12 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-info text-white"><h3 class="card-title">Categorías de uniformes</h3></div>
            <div class="card-body">
                <form method="POST" action="?r=guardar_categoria_uniforme" class="mb-4">
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-9 mb-md-0">
                            <label for="nueva_categoria">Nueva categoría</label>
                            <input type="text" id="nueva_categoria" name="nombre" class="form-control" maxlength="100" placeholder="Ej.: Uniforme reglamentario" required>
                        </div>
                        <div class="form-group col-md-3 mb-0">
                            <button type="submit" class="btn btn-success btn-block"><i class="fas fa-plus"></i> Agregar</button>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm mb-0">
                        <thead class="bg-secondary text-white"><tr><th>Nombre</th><th class="text-center">Ítems</th><th class="text-center">Acciones</th></tr></thead>
                        <tbody>
                            <?php if (empty($categorias)): ?>
                                <tr><td colspan="3" class="text-center text-muted">No hay categorías cargadas.</td></tr>
                            <?php else: ?>
                                <?php foreach ($categorias as $categoria): ?>
                                    <tr>
                                        <td><?= $esc($categoria['nombre']) ?></td>
                                        <td class="text-center"><?= (int) $categoria['cantidad_items'] ?></td>
                                        <td class="text-center text-nowrap">
                                            <button type="button" class="btn btn-warning btn-sm" title="Editar" data-toggle="modal" data-target="#editarCategoria<?= (int) $categoria['id'] ?>"><i class="fas fa-edit"></i></button>
                                            <form method="POST" action="?r=eliminar_categoria_uniforme" class="d-inline" onsubmit="return confirm('¿Eliminar esta categoría?');">
                                                <input type="hidden" name="id" value="<?= (int) $categoria['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar" <?= (int) $categoria['cantidad_items'] > 0 ? 'disabled' : '' ?>><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="editarCategoria<?= (int) $categoria['id'] ?>" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document"><div class="modal-content">
                                            <form method="POST" action="?r=guardar_categoria_uniforme">
                                                <div class="modal-header bg-warning"><h5 class="modal-title">Editar categoría</h5><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= (int) $categoria['id'] ?>">
                                                    <div class="form-group mb-0"><label>Nombre</label><input type="text" name="nombre" class="form-control" maxlength="100" value="<?= $esc($categoria['nombre']) ?>" required></div>
                                                </div>
                                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-success">Guardar cambios</button></div>
                                            </form>
                                        </div></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <small class="form-text text-muted mt-2">Las categorías con ítems asociados no se pueden eliminar.</small>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white"><h3 class="card-title">Ítems del catálogo</h3></div>
            <div class="card-body">
                <form method="POST" action="?r=guardar_item_uniforme" class="mb-4">
                    <div class="form-row">
                        <div class="form-group col-md-5"><label for="item_categoria">Categoría</label><select id="item_categoria" name="categoria_id" class="form-control" required><option value="">Seleccionar</option><?php foreach ($categorias as $categoria): ?><option value="<?= (int) $categoria['id'] ?>"><?= $esc($categoria['nombre']) ?></option><?php endforeach; ?></select></div>
                        <div class="form-group col-md-5"><label for="item_nombre">Nombre del ítem</label><input type="text" id="item_nombre" name="nombre" class="form-control" maxlength="100" placeholder="Ej.: Campera" required></div>
                        <div class="form-group col-md-2"><label for="item_estado">Estado</label><select id="item_estado" name="estado" class="form-control"><option value="activo">Activo</option><option value="inactivo">Inactivo</option></select></div>
                        <div class="form-group col-12"><label for="item_descripcion">Descripción</label><textarea id="item_descripcion" name="descripcion" class="form-control" rows="2" placeholder="Descripción opcional" data-optional="true"></textarea></div>
                    </div>
                    <button type="submit" class="btn btn-success" <?= empty($categorias) ? 'disabled' : '' ?>><i class="fas fa-save"></i> Guardar ítem</button>
                    <?php if (empty($categorias)): ?><small class="text-muted ml-2">Primero agregá una categoría.</small><?php endif; ?>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover table-sm mb-0">
                        <thead class="bg-secondary text-white"><tr><th>Ítem</th><th>Categoría</th><th>Estado</th><th class="text-center">Acciones</th></tr></thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr><td colspan="4" class="text-center text-muted">No hay ítems cargados.</td></tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?= $esc($item['nombre']) ?><?php if (!empty($item['descripcion'])): ?><br><small class="text-muted"><?= $esc($item['descripcion']) ?></small><?php endif; ?></td>
                                        <td><?= $esc($item['categoria']) ?></td>
                                        <td><span class="badge badge-<?= $item['estado'] === 'activo' ? 'success' : 'secondary' ?>"><?= $item['estado'] === 'activo' ? 'Activo' : 'Inactivo' ?></span></td>
                                        <td class="text-center text-nowrap">
                                            <button type="button" class="btn btn-warning btn-sm" title="Editar" data-toggle="modal" data-target="#editarItem<?= (int) $item['id'] ?>"><i class="fas fa-edit"></i></button>
                                            <form method="POST" action="?r=cambiar_estado_item_uniforme" class="d-inline"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><input type="hidden" name="estado" value="<?= $item['estado'] === 'activo' ? 'inactivo' : 'activo' ?>"><button type="submit" class="btn btn-<?= $item['estado'] === 'activo' ? 'secondary' : 'success' ?> btn-sm" title="<?= $item['estado'] === 'activo' ? 'Desactivar' : 'Activar' ?>"><i class="fas fa-<?= $item['estado'] === 'activo' ? 'ban' : 'check' ?>"></i></button></form>
                                            <form method="POST" action="?r=eliminar_item_uniforme" class="d-inline" onsubmit="return confirm('¿Eliminar este ítem? Si tiene entregas registradas, la operación será rechazada.');"><input type="hidden" name="id" value="<?= (int) $item['id'] ?>"><button type="submit" class="btn btn-danger btn-sm" title="Eliminar"><i class="fas fa-trash"></i></button></form>
                                        </td>
                                    </tr>

                                    <div class="modal fade" id="editarItem<?= (int) $item['id'] ?>" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document"><div class="modal-content">
                                            <form method="POST" action="?r=guardar_item_uniforme">
                                                <div class="modal-header bg-warning"><h5 class="modal-title">Editar ítem</h5><button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button></div>
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                                                    <div class="form-group"><label>Categoría</label><select name="categoria_id" class="form-control" required><?php foreach ($categorias as $categoria): ?><option value="<?= (int) $categoria['id'] ?>" <?= (int) $item['categoria_id'] === (int) $categoria['id'] ? 'selected' : '' ?>><?= $esc($categoria['nombre']) ?></option><?php endforeach; ?></select></div>
                                                    <div class="form-group"><label>Nombre</label><input type="text" name="nombre" class="form-control" maxlength="100" value="<?= $esc($item['nombre']) ?>" required></div>
                                                    <div class="form-group"><label>Descripción</label><textarea name="descripcion" class="form-control" rows="3"><?= $esc($item['descripcion']) ?></textarea></div>
                                                    <div class="form-group mb-0"><label>Estado</label><select name="estado" class="form-control"><option value="activo" <?= $item['estado'] === 'activo' ? 'selected' : '' ?>>Activo</option><option value="inactivo" <?= $item['estado'] === 'inactivo' ? 'selected' : '' ?>>Inactivo</option></select></div>
                                                </div>
                                                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-success">Guardar cambios</button></div>
                                            </form>
                                        </div></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
