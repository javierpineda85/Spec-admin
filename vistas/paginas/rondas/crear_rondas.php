<?php
// Inicia la sesión y carga datos necesarios (objetivos, etc.)
//session_start();
//$_SESSION['qr_codes']=[];
$db = new Conexion;
$sql = "SELECT * FROM objetivos ORDER BY nombre ";
$objetivos = $db->consultas($sql);
?>
<div class="card">
    <div class="card-header">
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
                <?php
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <p><i class="icon fas fa-check"></i>' . $_SESSION['success_message'] . '</p>
                        </div>';
                    // Elimina el mensaje después de mostrarlo
                    unset($_SESSION['success_message']);
                };
                ?>
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
                            <option value="Fijo">Fijo</option>
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
                <div id="qr-list" class="row">
                    <?php if (isset($_SESSION['qr_codes']) && !empty($_SESSION['qr_codes'])): ?>
                        <?php foreach ($_SESSION['qr_codes'] as $key => $qr): ?>
                            <div class="card col-3" data-key="<?php echo $key; ?>">
                                <img src="<?php echo htmlspecialchars($qr['image']); ?>" alt="Código QR">
                                <label for="" class="form-label"><?php echo htmlspecialchars($qr['data']); ?></label>
                                <div class="actions">

                                    <button class="delete-btn btn btn-danger" data-key="<?php echo $key; ?>">Eliminar</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No hay códigos QR generados aún.</p>
                    <?php endif; ?>
                </div>
                <br>

                <button class="btn btn-info"><a href="?r=imprimir_qr" target="_blank" class="text-white">Vista de Impresión</a> </button>
                <!-- Aquí podrías mostrar mensajes de éxito u otros avisos -->

            </div>
        </form>
    </div>
</div>

</section>
<!-- /.content -->
<!-- Script ajax para editar los card creados con los QR -->
<script>
    // Eliminar un QR
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault();
        var key = $(this).data('key');

        $.ajax({
            url: 'libraries/ajax/ajax_qr.php',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'delete',
                key: key
            },
            success: function(response) {
                if (response.success) {
                    // 1️⃣ Eliminar el QR del DOM
                    $('.card[data-key="' + key + '"]').remove();

                    // 2️⃣ Mostrar mensaje de éxito
                    $('#message').text('QR eliminado exitosamente').fadeIn().delay(2000).fadeOut();

                    // 3️⃣ Recargar la lista de QR sin recargar la página completa
                    setTimeout(function() {
                        $("#qr-list").load(location.href + " #qr-list > *", function() {
                            //console.log("Lista de QR recargada");
                        });
                    }, 500);

                } else {
                    alert('Error al eliminar el QR');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error en la petición AJAX:", error);
            }
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