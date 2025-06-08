<style>
    .card-qr {
        border: 1px solid #ddd;
        padding: 10px;
        margin: 10px;
        display: inline-block;
        width: 280px;
        text-align: center;
    }

    .card-qr img {
        max-width: 100%;
    }

    @media print {
        button {
            display: none;
        }
    }
</style>

<div class="card">
    <div class="card-header bg-info text-white">
        <h3 class="card-title ">Impresión de códigos QR</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div id="print-cards">
            <?php if (!empty($_SESSION['qr_codes'])): ?>
                <?php foreach ($_SESSION['qr_codes'] as $qr): ?>
                    <div class="card-qr"
                        data-id-ronda="<?= intval($qr['idRonda']) ?>"
                        data-puesto="<?= htmlspecialchars($qr['puesto']           ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        data-objetivo_id="<?= htmlspecialchars($qr['objetivo_id']    ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        data-objetivo_nombre="<?= htmlspecialchars($qr['objetivo_nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        data-tipo="<?= htmlspecialchars($qr['tipo']               ?? '', ENT_QUOTES, 'UTF-8') ?>"
                        data-orden_escaneo="<?= htmlspecialchars($qr['orden']       ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <img src="?r=mostrar_qr&ronda_id=<?= $qr['idRonda'] ?>&vigilador_id=<?= $_SESSION['idUsuario'] ?>&v=<?= time() ?>" alt="QR Ronda <?= $qr['orden'] ?>">
                        <div style="margin-top:8px;">
                            <p><strong>Objetivo:</strong> <?= htmlspecialchars($qr['objetivo_nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                            <p><strong>Puesto:</strong> <?= htmlspecialchars($qr['puesto'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                            <p><strong>Orden:</strong> <?= htmlspecialchars($qr['orden'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay códigos QR generados para imprimir.</p>
            <?php endif; ?>
        </div>
        <br>
        <button id="print-and-save" class="btn btn-info">Imprimir y guardar</button>
    </div>
</div>

<script>
    $('#print-and-save').click(function() {
        var qrData = [];
        $('.card-qr').each(function() {
            var $c = $(this);
            qrData.push({
                idRonda: $c.data('id-ronda'),
                puesto: $c.data('puesto'),
                objetivo_id: $c.data('objetivo_id'),
                tipo: $c.data('tipo'),
                orden_escaneo: $c.data('orden_escaneo')
            });
        });

        $.ajax({
                url: '?r=ajax_rondas',
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'save',
                    qrData: JSON.stringify(qrData)
                }
            })
            .done(function(resp) {
                if (resp.success) {
                    window.print();
                    if (window.opener && !window.opener.closed) {
                        window.opener.location.href = '?r=crear_rondas';
                    }
                    setTimeout(() => window.close(), 500);
                } else {
                    alert('Error guardando: ' + resp.error);
                }
            })
            .fail(function(xhr, status) {
                console.error(xhr.responseText);
                alert('Error en la petición AJAX: ' + status);
            });
    });
</script>