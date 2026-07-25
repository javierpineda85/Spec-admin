# Modo offline y sincronizacion

El sistema conserva en el telefono los registros operativos criticos cuando se
pierde la conexion:

- Marcaciones de entrada y salida.
- Reportes de Hombre Vivo.
- Escaneos QR de rondas.

Cada operacion se guarda en IndexedDB con un identificador unico y la fecha real
del evento. Al recuperar la conexion, el navegador intenta enviarla
automaticamente. Si el navegador soporta Background Sync, el service worker
tambien puede ejecutar el reenvio en segundo plano. En otros navegadores, la
sincronizacion se realiza al volver a abrir el sistema o al dispararse el evento
`online`.

## Comportamiento para el usuario

- Un aviso amarillo indica que el registro quedo guardado en el telefono.
- Un aviso verde confirma que los registros pendientes llegaron al servidor.
- La hora registrada es la hora original del evento, no la hora de reconexion.
- Reintentar una misma operacion no genera registros duplicados.

La pantalla que contiene el formulario debe haberse cargado antes de perder la
conexion. Las vistas dinamicas completas no se guardan para uso offline porque
contienen datos de sesion. El flujo de escaneo QR tiene una respuesta offline
propia siempre que el service worker ya este instalado.

## Implementacion

- `js/offline-store.js`: almacenamiento persistente en IndexedDB.
- `js/offline-sync.js`: envio, cola, reintentos y avisos al usuario.
- `service-worker.js`: Background Sync y captura offline de escaneos QR.
- Header `X-Spec-Operation-Id`: identificador idempotente compartido con el
  backend.
- Campo `operacion_id`: clave unica en las tres tablas sincronizables.

Los endpoints conservan la sesion como fuente de identidad. Los IDs de usuario
enviados por el cliente no se usan para reemplazar al usuario autenticado.

## Base de datos

Para una instalacion nueva, importar `spec.sql`.

Para una base existente, ejecutar una sola vez:

```text
scripts/migracion_offline_2026_07.sql
```

La cola queda en el dispositivo hasta recibir una respuesta exitosa. Si la
sesion vencio, los elementos se conservan y se vuelven a intentar despues de
iniciar sesion.
