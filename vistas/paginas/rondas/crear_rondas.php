<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$db = new Conexion;
$sql = "SELECT * FROM objetivos ORDER BY nombre ";
$objetivos = $db->consultas($sql);

?>
<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title">Completa los datos para generar una nueva ronda</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <!-- Aquí definimos el action para invocar el método del controlador Qr -->
        <form action="index.php?r=generar_qr" method="POST" class="form-horizontal">
            <div class="card-body">
                <?php if (!empty($_SESSION['success_message'])): ?>
                    <div class="alert alert-success alert-dismissible mt-3">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <i class="icon fas fa-check"></i>
                        <?= $_SESSION['success_message'];
                        unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>
                <div class="row">
                    <div class="form-group col-sm-12 col-md-3">
                        <label class="form-label">Sector / Puesto</label>
                        <input type="text" class="form-control" placeholder="Ronda 1" name="puesto">
                    </div>
                    <div class="form-group col-sm-12 col-md-3">
                        <label class="form-label">Objetivo</label>
                        <select id="objetivo" name="objetivo_id" class="form-control">
                            <option value="" disabled selected>Selecciona un objetivo</option>
                            <?php foreach ($objetivos as $objetivo): ?>
                                <option value="<?php echo $objetivo['idObjetivo'] ?>"><?php echo $objetivo['nombre'] ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="form-group col-sm-12 col-md-2">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" id="" class="form-control">
                            <option value="" selected disabled>Elige una opción</option>
                            <option value="Fija">Fija</option>
                            <option value="Eventual">Eventual</option>
                        </select>
                    </div>
                    <div class="form-group col-sm-12 col-md-2">
                        <label class="form-label">Orden de escaneo</label>
                        <input type="number" name="orden" id="orden" class="form-control">
                    </div>
                    <div class="form-group col-sm-12 col-md-2 mt-2">
                        <button type="submit" class="btn btn-success mt-4">Generar QR</button>

                    </div>
                </div>
            </div>
            <!-- Aquí puedes incluir el listado de QR generados (cards) -->
            <div class="card-footer">
                <?php if (!empty($_SESSION['qr_codes'])): ?>
                    <div class="row">
                        <?php foreach ($_SESSION['qr_codes'] as $key => $qr): ?>
                            <div class="card col-md-3 text-center mb-4" data-key="<?= $key ?>">
                                <img src="?r=mostrar_qr&ronda_id=<?= $qr['idRonda'] ?>&vigilador_id=<?= $_SESSION['idUsuario'] ?>&v=<?= time() ?>" class="img-fluid mb-2" alt="QR Ronda <?= $qr['orden'] ?>" />
                                <div class="card-body">
                                    <p><strong>Objetivo:</strong> <?= htmlspecialchars($qr['objetivo_nombre']) ?></p>
                                    <p><strong>Puesto:</strong> <?= htmlspecialchars($qr['puesto']) ?></p>
                                    <p><strong>Orden:</strong> <?= htmlspecialchars($qr['orden']) ?></p>
                                    <!-- Ahora $key está definido -->
                                    <button class="btn btn-danger btn-sm delete-qr" data-key="<?= $key ?>">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <button class="btn btn-info"><a href="?r=imprimir_qr" target="_blank" class="text-white">Vista de Impresión</a> </button>

            </div>
        </form>
    </div>
</div>

<!-- Script ajax para editar los card creados con los QR -->
<script>
    // Eliminar un QR
    $(document).on('click', '.delete-qr', function(e) {
        e.preventDefault();
        const key = $(this).data('key');
        $.ajax({
                url: 'index.php?r=delete_qr',
                method: 'POST',
                data: {
                    key: key
                },
                dataType: 'json'
            })
            .done(function(resp) {
                if (resp.success) {
                    $('.card[data-key="' + key + '"]').remove();
                    // Reindexar cards
                    $('.row > .card').each(function(i, card) {
                        $(card).attr('data-key', i);
                    });
                } else {
                    alert('No se pudo eliminar el QR.');
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX error:', textStatus, errorThrown, jqXHR.responseText);
                alert('Error en la petición: ' + textStatus);
            });
    });

    // Guardar todos los QR en la base de datos
    $('#save-all').click(function() {
        $.ajax({
            url: 'save_all.php',
            type: 'POST',
            success: function(response) {
                //var res = JSON.parse(response);
                if (response.success) {
                    $('#message').text('Todos los QR se han guardado en la base de datos').fadeIn().delay(2000).fadeOut();
                    // Opcional: limpiar la lista o redirigir
                } else {
                    alert('Error al guardar en la base de datos');
                }
            }
        });
    });
</script>