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
        <button onclick="window.print()" class="btn btn-info">Imprimir</button>
    </div>
</div>