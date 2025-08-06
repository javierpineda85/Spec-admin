self.addEventListener('install', event => {
  self.skipWaiting();

  const base = '/' + self.location.pathname.split('/')[1]; // Detecta el subdirectorio (ej. /Spec-admin)

  const archivos = [
    `${base}/`,
    `${base}/css/spec.css`,
    `${base}/css/adminlte.min.css`,
    `${base}/css/dataTables.bootstrap4.min.css`,
    `${base}/css/buttons.bootstrap4.min.css`,
    `${base}/css/responsive.bootstrap4.min.css`,
    `${base}/js/main.js`,
    `${base}/public/sonidos/spec_notificacion.mp3`
  ];

  event.waitUntil(
    caches.open('spec-cache-v1').then(cache => {
      return Promise.all(
        archivos.map(url =>
          cache.add(url).catch(err => console.warn(`❌ No se pudo cachear: ${url}`, err))
        )
      );
    })
  );
});

self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request).then(response => {
      return response || fetch(event.request);
    })
  );
});

// ===========================================
// 🔔 Notificaciones Push
// ===========================================

self.addEventListener('push', event => {
  console.log('📩 Push recibido:', event);

  let data = {};
  try {
    data = event.data.json();
  } catch (e) {
    console.warn('⚠️ Push no contiene JSON válido');
  }

  const title = data.title || 'Nueva alerta';
  const options = {
    body: data.body || 'Tienes una nueva notificación en SPEC.',
    icon: '/Spec-admin/public/img/icons/icon-192.png',
    badge: '/Spec-admin/public/img/icons/icon-192.png',
    vibrate: [200, 100, 200],
    data: {
      url: data.url || 'index.php'
    }
  };

  event.waitUntil(
    self.registration.showNotification(title, options)
  );
});

// 🔁 Al hacer clic en la notificación
self.addEventListener('notificationclick', function(event) {
  event.notification.close();

  // Redirigir al sistema
  event.waitUntil(
    clients.matchAll({ type: "window", includeUncontrolled: true }).then(function(clientList) {
      for (const client of clientList) {
        if ('focus' in client) {
          if (event.notification.data && event.notification.data.url) {
            client.navigate(event.notification.data.url);
          }
          return client.focus();
        }
      }

      // Si no hay ventanas abiertas
      if (clients.openWindow && event.notification.data && event.notification.data.url) {
        return clients.openWindow(event.notification.data.url);
      }
    })
  );
});
