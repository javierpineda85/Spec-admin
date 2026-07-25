(function (window, document) {
  'use strict';

  const store = window.SpecOfflineStore;
  let flushing = false;

  function showToast(message, type) {
    if (typeof window.mostrarToast === 'function') {
      window.mostrarToast(message, type || 'info');
      return;
    }

    if (window.Toastify) {
      const colors = {
        success: '#28a745',
        warning: '#f0ad4e',
        danger: '#dc3545',
        info: '#17a2b8'
      };
      window.Toastify({
        text: message,
        duration: 6000,
        close: true,
        gravity: 'top',
        position: 'right',
        style: { background: colors[type] || colors.info }
      }).showToast();
    }
  }

  async function registerBackgroundSync() {
    if (!('serviceWorker' in navigator)) {
      return;
    }

    try {
      const registration = await navigator.serviceWorker.ready;
      if ('sync' in registration) {
        await registration.sync.register('spec-offline-sync');
      }
    } catch (error) {
      console.warn('No se pudo programar la sincronizacion offline:', error);
    }
  }

  async function queueRequest(request) {
    await store.put(request);
    await registerBackgroundSync();
    document.dispatchEvent(new CustomEvent('spec:offline-queued', { detail: request }));
    showToast('Sin señal: el registro quedó guardado en este teléfono.', 'warning');
    return { queued: true, operationId: request.id };
  }

  function buildRequest(options) {
    const id = options.operationId || store.createId();
    const headers = Object.assign({}, options.headers || {});
    headers['X-Spec-Operation-Id'] = id;

    return {
      id,
      type: options.type || 'registro',
      url: new URL(options.url, window.location.href).toString(),
      method: options.method || 'POST',
      headers,
      body: options.body == null ? null : String(options.body),
      createdAt: options.createdAt || new Date().toISOString()
    };
  }

  async function send(options) {
    const request = buildRequest(options);

    if (!navigator.onLine) {
      return queueRequest(request);
    }

    try {
      const response = await fetch(request.url, {
        method: request.method,
        headers: request.headers,
        body: request.body,
        credentials: 'include'
      });

      if (!response.ok && response.status >= 500) {
        return queueRequest(request);
      }

      return { queued: false, response, operationId: request.id };
    } catch (error) {
      return queueRequest(request);
    }
  }

  async function flush() {
    if (flushing || !navigator.onLine) {
      return;
    }

    flushing = true;
    let synced = 0;

    try {
      const pending = await store.getAll();
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
            await store.remove(request.id);
            synced++;
            document.dispatchEvent(new CustomEvent('spec:offline-synced', { detail: request }));
          }
        } catch (_) {
          break;
        }
      }
    } finally {
      flushing = false;
    }

    if (synced > 0) {
      showToast(
        synced === 1
          ? 'El registro pendiente se sincronizó correctamente.'
          : `${synced} registros pendientes se sincronizaron correctamente.`,
        'success'
      );
    }
  }

  window.SpecOffline = { send, flush, createId: store.createId };

  window.addEventListener('online', flush);
  document.addEventListener('DOMContentLoaded', flush);

  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.addEventListener('message', event => {
      if (event.data && event.data.type === 'SPEC_OFFLINE_SYNCED') {
        const count = Number(event.data.count || 0);
        if (count > 0) {
          showToast(
            count === 1
              ? 'El registro pendiente se sincronizó correctamente.'
              : `${count} registros pendientes se sincronizaron correctamente.`,
            'success'
          );
        }
        flush();
      }
    });
  }
})(window, document);
