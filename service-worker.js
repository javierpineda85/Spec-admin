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
  const url = new URL(event.request.url);

  // Si la URL tiene parámetro "r", es una ruta dinámica → no cachear
  if (url.searchParams.has('r')) {
    return; // dejar que el navegador haga la request normal
  }

  // Para todo lo demás (assets estáticos)
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
  event.waitUntil((async () => {
    const base = new URL(self.registration.scope);
    const alertasUrl = new URL('ajax/ver_alertas.php', base).toString();
    const destinoAlertas = new URL('index.php?r=alertas_supervisor', base).toString();
    let title = 'Nueva alerta en SPEC';
    let body = 'Tienes una notificación pendiente.';
    let url = destinoAlertas;

    try {
      if (event.data) {
        const texto = await event.data.text();
        if (texto) {
          try {
            const payload = JSON.parse(texto);
            if (payload.title) title = payload.title;
            if (payload.body) body = payload.body;
            if (payload.url) url = new URL(payload.url, base).toString();
          } catch (_) {
            body = texto;
          }
        }
      }

      const response = await fetch(alertasUrl, { credentials: 'include' });
      if (response.ok) {
        const alertas = await response.json();
        if (Array.isArray(alertas) && alertas.length > 0) {
          const alerta = alertas[0];
          const tipo = String(alerta.tipo || 'alerta').toUpperCase();
          title = `${tipo} en SPEC`;
          body = String(alerta.mensaje || body).replace(/\s+/g, ' ').trim();
          if (body.length > 140) {
            body = body.slice(0, 137) + '...';
          }

          if (String(alerta.tipo || '') === 'directiva') {
            url = new URL('index.php?r=listado_directivas', base).toString();
          } else {
            url = destinoAlertas;
          }
        }
      }
    } catch (error) {
      console.warn('No se pudo armar la notificacion push:', error);
    }

    await self.registration.showNotification(title, {
      body,
      icon: '/Spec-admin/public/img/icons/icon-192.png',
      badge: '/Spec-admin/public/img/icons/icon-192.png',
      vibrate: [200, 100, 200],
      data: { url }
    });
  })());
});

// 🔁 Al hacer clic en la notificación
self.addEventListener('notificationclick', function (event) {
  event.notification.close();

  // Redirigir al sistema
  event.waitUntil(
    clients.matchAll({ type: "window", includeUncontrolled: true }).then(function (clientList) {
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
