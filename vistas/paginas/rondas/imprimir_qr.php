<style>
    .card-qr {
        border: 1px solid #ddd;
        padding: 10px;
        margin: 10px;
        display: inline-block;
        width: 280px;
        text-align: center;
    }

    .card img {
        max-width: 100%;
    }

    @media print {
        button {
            display: none;
        }
    }
</style>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Impresion de códigos QR</h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    <div class="card-body">
        <div id="print-cards ">
            <?php if (isset($_SESSION['qr_codes']) && !empty($_SESSION['qr_codes'])): ?>
                <?php foreach ($_SESSION['qr_codes'] as $qr): ?>
                    <div class="card-qr">
                        <img src="<?php echo htmlspecialchars($qr['image']); ?>" alt="Código QR">
                        <p><?php echo htmlspecialchars($qr['data']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay códigos QR generados para imprimir.</p>
            <?php endif; ?>
        </div>
        <br>
        <button onclick="window.print()" class="btn btn-info" id="print-and-save">Imprimir y guardar</button>
    </div>
</div>
<script>
$("#print-and-save").click(function () {
    var qrData = [];

    $(".card-qr").each(function () {
        var data = $(this).find("p").text();
        var image = $(this).find("img").attr("src");

        var matches = data.match(/Sector:\s(.*?)\s-\sObjetivo:\s(.*?)\s-\sOrden:\s(.*?)\s-/);
        if (matches) {
            qrData.push({
                puesto: matches[1],
                objetivo_id: matches[2],
                orden_escaneo: matches[3],
                tipo: "Fija"
            });
        }
    });

    $.ajax({
        url: 'libraries/ajax/ajax_rondas.php',
        type: 'POST',
        dataType: 'json',
        data: {
            action: 'save',
            qrData: JSON.stringify(qrData)
        },
        success: function (response) {
            console.log("🚀 Respuesta del servidor:", response);

            if (response.success) {
                alert("✅ Rondas guardadas correctamente.");
                window.print();
            } else {
                alert("❌ Error en la base de datos: " + response.error);
            }
        },
        error: function (xhr, status, error) {
            console.error("❌ Error en AJAX:", xhr.responseText);
        }
    });
});
</script>