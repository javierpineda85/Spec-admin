// Registro del Service Worker y suscripcion a notificaciones Push

function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/-/g, '+')
    .replace(/_/g, '/');

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);
  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

async function registrarSuscripcionPush(registro) {
  const config = window.SPEC_PUSH || {};

  if (!config.enabled || !config.publicKey || !config.subscribeUrl) {
    return;
  }

  const permiso = await Notification.requestPermission();
  if (permiso !== 'granted') {
    console.warn('Permiso de notificaciones denegado');
    return;
  }

  let suscripcion = await registro.pushManager.getSubscription();
  if (!suscripcion) {
    suscripcion = await registro.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: urlBase64ToUint8Array(config.publicKey)
    });
  }

  if (!suscripcion) {
    return;
  }

  await fetch(config.subscribeUrl, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    credentials: 'include',
    body: JSON.stringify(suscripcion)
  });
}

if ('serviceWorker' in navigator && 'PushManager' in window) {
  window.addEventListener('load', async () => {
    try {
      const registro = await navigator.serviceWorker.register('service-worker.js');
      await registrarSuscripcionPush(registro);
    } catch (error) {
      console.error('Error al registrar SW o Push:', error);
    }
  });
} else {
  console.warn('Service Worker o PushManager no soportados por este navegador');
}
