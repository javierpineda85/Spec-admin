// ✅ Registro del Service Worker y suscripción a notificaciones Push

if ('serviceWorker' in navigator && 'PushManager' in window) {
  window.addEventListener('load', async () => {
    try {
      const registro = await navigator.serviceWorker.register('service-worker.js');
      //console.log('✅ Service Worker registrado:', registro);

      // ⏳ Pedir permiso para notificaciones
      const permiso = await Notification.requestPermission();
      if (permiso !== 'granted') {
        console.warn('⚠️ Permiso de notificaciones denegado');
        return;
      }

      // 🔐 Suscripción al PushManager (aún sin servidor, simulamos)
      const subscripcion = await registro.pushManager.getSubscription();
      if (!subscripcion) {
        //console.log('🔔 Usuario no suscrito aún (modo demo sin VAPID)');
        // Si usaras VAPID, aquí deberías suscribirlo con una clave pública
      } else {
        console.log('🔔 Usuario ya está suscrito:', subscripcion);
      }

    } catch (error) {
      console.error('❌ Error al registrar SW o pedir permiso:', error);
    }
  });
} else {
  console.warn('❌ Service Worker o PushManager no soportados por este navegador');
}
