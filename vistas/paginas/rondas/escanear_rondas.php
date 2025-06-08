
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
  // Instancia del lector de códigos QR
  const codeReader = new ZXing.BrowserQRCodeReader();
  const deviceList = document.getElementById('deviceList');

  // Enumerar cámaras disponibles
  codeReader.listVideoInputDevices()
    .then(devices => {
      if (devices.length === 0) {
        document.getElementById('mensaje').innerText = 'No se encontró cámara.';
        return;
      }
      devices.forEach(device => {
        const option = document.createElement('option');
        option.value = device.deviceId;
        option.text = device.label || `Cámara ${deviceList.length + 1}`;
        deviceList.appendChild(option);
      });
      // Iniciar con la primera cámara
      startScanner(deviceList.value);
    })
    .catch(err => {
      console.error(err);
      document.getElementById('mensaje').innerText = 'Error al acceder a la cámara.';
    });

  // Al cambiar la selección de cámara
  deviceList.addEventListener('change', () => {
    codeReader.reset();
    startScanner(deviceList.value);
  });

  // Función para iniciar la captura de video y decodificación
  function startScanner(deviceId) {
    codeReader.decodeFromVideoDevice(deviceId, 'preview', (result, err) => {
      if (result) {
        codeReader.reset();
        window.location.href = result.getText();
      }
    });
  }
</script>
