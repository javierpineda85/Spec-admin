<?php
$usuarioEntrega = $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];
//Nuevo formato de mes
$formatter = new IntlDateFormatter(
    'es_AR',
    IntlDateFormatter::LONG,
    IntlDateFormatter::NONE,
    'America/Argentina/Buenos_Aires',
    IntlDateFormatter::GREGORIAN,
    'MMMM'
);

$mes = $formatter->format(new DateTime());

$config = (new Conexion)->consultas("SELECT * FROM configuracion ")[0];


?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Comprobante de entrega de uniforme</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
        }

        h4 {
            text-align: center;
            margin-top: 0;
        }

        .right {
            text-align: right;

        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #000;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        .firma {
            margin-top: 60px;
        }

        .firma div {
            width: 45%;
            display: inline-block;
            text-align: center;
        }

        .texto-legal {
            margin-top: 30px;
            font-size: 13px;
            text-align: justify;
        }

        @media print {

            footer,
            .main-footer {
                display: none !important;
            }
        }
    </style>

</head>

<body>

    <h2>ENTREGA DE UNIFORME</h2>
    <h4><?= $config['valor'] ?></h4>

    <p class="right">Mendoza, <?= date('d') ?> de <?= $mes ?> de <?= date('Y') ?></p>

    <p>Siendo las <?= date('H:i') ?>hs, la empresa <?= $config['valor'] ?> hace entrega de la siguiente indumentaria y/o equipos a <?= $usuario['apellido'] . ', ' . $usuario['nombre'] ?></p>
    <p> con <strong>DNI:</strong> <?= $usuario['dni'] ?> según se detalla a continuación:</p>

    <h3>Indumentaria provista y equipos</h3>

    <table>
        <thead>
            <tr>
                <th>Prenda / Equipo</th>
                <th>Cantidad</th>
                <th>Talle</th>
                <th>Observaciones</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($entregas as $e): ?>
                <tr>
                    <td><?= $e['item_nombre'] ?: $e['item_libre'] ?></td>
                    <td><?= $e['cantidad'] ?></td>
                    <td><?= $e['talle'] ?></td>
                    <td><?= $e['observaciones'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="texto-legal">
        Quien recibe uniforme provisto nuevo, al momento de su devolución deberá ser entregado en perfecto estado de uso y conservación, tal como le fue entregado oportunamente.
        En caso de que dicha vestimenta se encuentre dañada o en mal estado, se procederá al descuento del valor pecuniario del mismo.
        En caso de extravío de alguno de los ítems, o los cuales sufrieran algún tipo de daño, serán descontados su valor económico al causante.
    </div>

    <div class="firma">
        <div>
            ______________________________<br>
            Firma del empleado
        </div>

        <div>
            ______________________________<br>
            Aclaración
        </div>
    </div>
    <div class="firma">

        <div>
            ______________________________<br>
            Entregado por: <?= $usuarioEntrega ?>
        </div>
    </div>
    <script>
        window.print();
    </script>

</body>

</html>