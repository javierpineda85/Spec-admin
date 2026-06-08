<?php
//Auth::check('art', 'verCredencialArt');

$idUsuario = $_SESSION['idUsuario'] ?? 0;
require_once('modelos/art.modelo.php');
// Obtener última ART registrada
$art = ModeloArt::mdlObtenerUltimaArt(); 

// Datos del usuario y grupo sanguíneo
$db = new Conexion;
$empleado = $db->consultas("SELECT nombre, apellido, nombre_contacto, parentesco, tel_emergencia, imgPerfil FROM usuarios WHERE idUsuario = $idUsuario LIMIT 1")[0] ?? null;
$g_sanguineo = $db->consultas("SELECT grupo_sanguineo FROM salud WHERE usuario_id = $idUsuario LIMIT 1");

if (!$art || !$empleado):
?>
  <div class="alert alert-warning">No hay información de A.R.T. registrada.</div>
<?php else: ?>
  <div class="card card-info mx-auto" style="max-width: 600px;" id="credencial-art">
    <div class="card-header bg-success text-white text-center py-2">
      <h5 class="mb-0">CREDENCIAL A.R.T.</h5>
    </div>

    <div class="card-body row">
      <!-- Columna imagen -->
      <div class="col-12 col-md-4 text-center mb-3 mb-md-0">
        <img src="<?= $empleado['imgPerfil'] ?? 'img/user-placeholder.png' ?>" 
             class="img-fluid border" 
             style="width: 100%; max-width: 140px; border-radius: 10px;" 
             alt="Foto">
      </div>

      <!-- Columna datos -->
      <div class="col-12 col-md-8">
        <h5 class="text-info"><?= $empleado['apellido'] . ', ' . $empleado['nombre'] ?></h5>

        <p class="mb-1"><strong>Razón Social:</strong> <?= htmlspecialchars($art['razon_social'])?? '' ?></p>
        <p class="mb-1"><strong>CUIT:</strong> <?= $art['cuit_empresa'] ?></p>
        <p class="mb-1"><strong>Tel. Empresa:</strong> 
          <a href="tel:<?= preg_replace('/\D+/', '', $art['telefono_empresa']) ?>" class="text-secondary">
            <?= $art['telefono_empresa'] ?>
          </a>
        </p>

        <p class="mb-1"><strong>Aseguradora:</strong> <?= htmlspecialchars($art['empresa_aseguradora']) ?? ''?></p>
        <p class="mb-1"><strong>CUIT Aseguradora:</strong> <?= $art['cuit_aseguradora'] ?></p>
        <p class="mb-1"><strong>Póliza:</strong> <?= $art['nro_poliza'] ?></p>
        <p class="mb-1"><strong>Tel. Aseguradora:</strong> 
          <a href="tel:<?= preg_replace('/\D+/', '', $art['telefono_aseguradora']) ?>" class="text-danger">
            <?= $art['telefono_aseguradora'] ?>
          </a>
        </p>

        <p class="mb-1"><strong>Grupo Sanguíneo:</strong> <?= $g_sanguineo[0]['grupo_sanguineo'] ?? 'No informado' ?></p>
        <p class="mb-1"><strong>Contacto de Emergencia:</strong> <?= $empleado['nombre_contacto'] ?> (<?= $empleado['parentesco'] ?>)</p>
        <p class="mb-0"><strong>Tel. Emergencia:</strong> 
          <a href="tel:<?= preg_replace('/\D+/', '', $empleado['tel_emergencia']) ?>" class="text-secondary">
            <?= $empleado['tel_emergencia'] ?>
          </a>
        </p>
      </div>
    </div>

    <div class="card-footer text-center bg-light small text-muted">
      SPEC | Grupo Marzan
    </div>
  </div>

  <!-- Botón descarga -->
  <div class="text-center mt-3">
    <button class="btn btn-primary btn-sm" onclick="descargarCredencial()">Descargar como imagen</button>
  </div>

  <!-- Script para generar imagen -->
  <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
  <script>
    function descargarCredencial() {
      const credencial = document.getElementById('credencial-art');
      html2canvas(credencial).then(canvas => {
        const link = document.createElement('a');
        link.download = 'credencial_art.png';
        link.href = canvas.toDataURL();
        link.click();
      });
    }
  </script>
<?php endif; ?>
