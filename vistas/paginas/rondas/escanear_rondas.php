<div class="card">
  <div class="card-header bg-info text-white">
    <h3 class="card-title">Escanear Ronda</h3>
  </div>
  <div class="card-body">
    <p id="mensaje">Apunta al código QR para registrar el escaneo.</p>
    <div class="form-group">
      <label for="deviceList">Selecciona cámara:</label>
      <select id="deviceList" class="form-control mb-2"></select>
    </div>
    <video id="preview" style="width:100%;height:auto;" autoplay muted playsinline></video>
  </div>
</div>

<script src="https://unpkg.com/@zxing/library@latest"></script>
<script>
  
    document.addEventListener('DOMContentLoaded', async () => {
    const codeReader = new ZXing.BrowserQRCodeReader();
    const deviceList = document.getElementById('deviceList');
    const mensaje = document.getElementById('mensaje');
    const videoElem = document.getElementById('preview');

    // 1) Pide permiso a la cámara antes de listar
    try {
      await navigator.mediaDevices.getUserMedia({
        video: true
      });
    } catch (err) {
      console.error('No se pudo obtener permiso de cámara:', err);
      mensaje.innerText = 'Permiso de cámara denegado.';
      return;
    }

    // 2) Ahora sí, lista las cámaras
    try {
      const devices = await codeReader.listVideoInputDevices();
      if (devices.length === 0) {
        mensaje.innerText = 'No se encontró ninguna cámara.';
        return;
      }
      devices.forEach((d, i) => {
        const opt = document.createElement('option');
        opt.value = d.deviceId;
        // Puede que label esté vacío en algunos navegadores la primera vez
        opt.text = d.label || `Cámara #${i+1}`;
        deviceList.appendChild(opt);
      });
      // Arranca el scanner con la primera cámara
      startScanner(devices[0].deviceId);
    } catch (err) {
      console.error('Error al enumerar dispositivos de video:', err);
      mensaje.innerText = 'Error al acceder a la cámara.';
    }

    // 3) Resetea y arranca con la cámara seleccionada
    deviceList.addEventListener('change', () => {
      codeReader.reset();
      startScanner(deviceList.value);
    });

    // 4) Función para arrancar la captura
    function startScanner(deviceId) {
      mensaje.innerText = 'Buscando código…';
      codeReader.decodeFromVideoDevice(deviceId, videoElem, (result, err) => {
        if (result) {
          codeReader.reset();
          window.location.href = result.getText();
        }
        // opcional: mostrar error de lectura intermitente
        if (err && !(err instanceof ZXing.NotFoundException)) {
          console.error(err);
        }
      });
    }
  });
</script>