<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header bg-danger">
          <h3 class="card-title text-white">Gestión de Permisos</h3>
        </div>
        <div class="card-body">
          <div class="card bg-warning ">
              <p class="p-3 text center"> <i class="fas fa-exclamation-triangle"></i> No realizar ningún cambio si no está seguro de como funciona el permiso. Esto puede afectar gravemente al sistema. <i class="fas fa-exclamation-triangle"></i></p>
          </div>
          <?php if(!empty($_SESSION['success_message'])): ?>
            <div class="alert alert-success">
              <?= $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
          <?php endif; ?>

          <form action="?r=permisos/update" method="post">
            <div class="form-group">
              <label for="roleSelect">Selecciona un rol:</label>
              <select
                name="role"
                id="roleSelect"
                class="form-control"
                onchange="location = '?r=permisos&role='+this.value"
              >
                <?php foreach($roles as $r): ?>
                  <option
                    value="<?= htmlspecialchars($r['rol']); ?>"
                    <?= $r['rol']==$selectedRole ? 'selected':''; ?>
                  >
                    <?= htmlspecialchars($r['rol']); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>

            <table class="table table-bordered table-striped">
              <thead>
                <tr>
                  <th>Controlador</th>
                  <th>Acción</th>
                  <th>Descripción</th>
                  <th style="text-align:center;">Asignar</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($permissions as $p): ?>
                  <tr>
                    <td><?= htmlspecialchars($p['controlador']?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars($p['accion']?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars($p['descripcion']?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                    <td style="text-align:center;">
                      <input
                        type="checkbox"
                        name="permissions[]"
                        value="<?= $p['id']; ?>"
                        <?= in_array($p['id'],$assignedIds)?'checked':''; ?>
                      >
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>

            <button type="submit" class="btn btn-primary">Guardar cambios</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
