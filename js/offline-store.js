(function (scope) {
  'use strict';

  const DB_NAME = 'spec-admin-offline';
  const DB_VERSION = 1;
  const STORE_NAME = 'pending_requests';

  function openDatabase() {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open(DB_NAME, DB_VERSION);

      request.onupgradeneeded = () => {
        const db = request.result;
        if (!db.objectStoreNames.contains(STORE_NAME)) {
          const store = db.createObjectStore(STORE_NAME, { keyPath: 'id' });
          store.createIndex('createdAt', 'createdAt', { unique: false });
        }
      };

      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
    });
  }

  function withStore(mode, callback) {
    return openDatabase().then(db => new Promise((resolve, reject) => {
      const transaction = db.transaction(STORE_NAME, mode);
      const store = transaction.objectStore(STORE_NAME);
      const request = callback(store);

      request.onsuccess = () => resolve(request.result);
      request.onerror = () => reject(request.error);
      transaction.oncomplete = () => db.close();
      transaction.onerror = () => reject(transaction.error);
    }));
  }

  function createId() {
    if (scope.crypto && typeof scope.crypto.randomUUID === 'function') {
      return scope.crypto.randomUUID();
    }

    return 'spec-' + Date.now() + '-' + Math.random().toString(16).slice(2);
  }

  scope.SpecOfflineStore = {
    createId,
    put(item) {
      return withStore('readwrite', store => store.put(item));
    },
    remove(id) {
      return withStore('readwrite', store => store.delete(id));
    },
    getAll() {
      return withStore('readonly', store => store.getAll()).then(items =>
        items.sort((a, b) => String(a.createdAt).localeCompare(String(b.createdAt)))
      );
    }
  };
})(typeof self !== 'undefined' ? self : window);
