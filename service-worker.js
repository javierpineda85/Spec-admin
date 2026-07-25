importScripts('js/offline-store.js');

const CACHE_NAME = 'spec-cache-v2';

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
    `${base}/js/offline-store.js`,
    `${base}/js/offline-sync.js`,
    `${base}/public/sonidos/spec_notificacion.mp3`
  ];

  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      return Promise.all(
        archivos.map(url =>
          cache.add(url).catch(err => console.warn(`❌ No se pudo cachear: ${url}`, err))
        )
      );
    })
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys()
      .then(keys => Promise.all(keys
        .filter(key => key.startsWith('spec-cache-') && key !== CACHE_NAME)
        .map(key => caches.delete(key))))
      .then(() => self.clients.claim())
  );
});

function requestToRecord(request, type) {
  const operationId = SpecOfflineStore.createId();
  const url = new URL(request.url);
  url.searchParams.set('operacion_id', operationId);
  url.searchParams.set('fecha_evento', new Date().toISOString());
  url.searchParams.set('format', 'json');

  return {
    id: operationId,
    type,
    url: url.toString(),
    method: 'GET',
    headers: { 'X-Spec-Operation-Id': operationId },
    body: null,
    createdAt: new Date().toISOString()
  };
}

function offlineSavedResponse() {
  return new Response(`<!doctype html>
    <html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Registro guardado</title>
    <style>body{font-family:Arial,sans-serif;background:#f4f6f9;margin:0;padding:24px;color:#263238}
    main{max-width:440px;margin:15vh auto;background:#fff;padding:24px;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,.12)}
    h1{font-size:22px;color:#b77900}a{display:inline-block;margin-top:12px;color:#087f8c}</style></head>
    <body><main><h1>Escaneo guardado en el teléfono</h1>
    <p>No hay señal. Se enviará automáticamente cuando vuelva la conexión.</p>
    <a href="javascript:history.back()">Volver</a></main></body></html>`, {
    status: 202,
    headers: { 'Content-Type': 'text/html; charset=utf-8' }
  });
}

async function handleOfflineEscaneo(request) {
  try {
    return await fetch(request);
  } catch (_) {
    const record = requestToRecord(request, 'escaneo_ronda');
    await SpecOfflineStore.put(record);

    if (self.registration.sync) {
      await self.registration.sync.register('spec-offline-sync').catch(() => {});
    }

    return offlineSavedResponse();
  }
}

self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);

  if (event.request.method === 'GET' && url.searchParams.get('r') === 'registrar_escaneo') {
    event.respondWith(handleOfflineEscaneo(event.request));
    return;
  }

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

async function flushOfflineRequests() {
  const pending = await SpecOfflineStore.getAll();
  let synced = 0;

  for (const request of pending) {
    try {
      const response = await fetch(request.url, {
        method: request.method,
        headers: request.headers,
        body: request.body,
        credentials: 'include'
      });

      if (response.status === 401 || response.status === 403) {
        break;
      }

      if (response.ok) {
        await SpecOfflineStore.remove(request.id);
        synced++;
      }
    } catch (_) {
      break;
    }
  }

  if (synced > 0) {
    const clientList = await clients.matchAll({ type: 'window', includeUncontrolled: true });
    clientList.forEach(client => client.postMessage({ type: 'SPEC_OFFLINE_SYNCED', count: synced }));
  }
}

self.addEventListener('sync', event => {
  if (event.tag === 'spec-offline-sync') {
    event.waitUntil(flushOfflineRequests());
  }
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
