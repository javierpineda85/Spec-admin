# Pruebas de armado y continuidad de cronogramas

**Resultado: el armado PHP conserva el mes y completa el 4×2 básico, pero la tabla no conserva toda la muestra.**

Se ejecutó el código original con datos de mayo de 2025 en memoria. No se escribió en MySQL, no se alteró el Excel y no se modificó código funcional. GP/D y GP/N mantienen 12 horas.

## Resultados

- PHP: 48/48 comprobaciones correctas (carga, estructura mensual, continuidad, roles y horas GP).
- JavaScript: 30/47 correctas y 17 fallidas. Los fallos se agrupan en pérdida de vínculos de nómina y pérdida de códigos históricos.
- Las 4.019 celdas con código de mayo se recuperaron sin alteraciones en el controlador.
- Junio se generó con las 173 filas y 30 días por fila: 5.190 celdas.
- En el HTML de mayo faltan 40 filas, que contienen 556 celdas con código. En las filas presentes, otras 412 celdas quedan sin su código original.
- Las dos pérdidas no se solapan: 968 de las 4.019 celdas con código no quedan representadas con su contenido original en esta simulación. No se ejecutó un guardado posterior.

## Qué pasó las pruebas

- Recarga de mayo, sin regenerar el mes ni perder códigos en PHP, para los 13 cuadros.
- Selección del mes anterior y estructura de junio para cada cuadro.
- Los seis estados de cola del ciclo D,D,N,N,F,F para vigiladores y referentes.
- Febrero de 28 y 29 días, meses de 30 y 31 días, y paso diciembre–enero.
- Alta simulada sin historial: comienza D,D,N,N,F,F. Exclusión de personal inactivo en la generación nueva.
- La nómina completa ejecutada también como Referente; es un escenario de prueba, no la atribución de ese rol histórico.
- Horas unitarias GP/D=12, GP/N=12, 10H=10, 12H=12 y F=0.
- Una sigla disponible en el catálogo simulado se conserva como opción seleccionada.

## Fallos reproducidos

### 1. Se pierden vínculos de empleados entre objetivos

`js/cronograma.js:106–120` elimina duplicados por idUsuario en la lista completa. Luego `renderTabla` filtra esa lista por objetivo. Una persona vinculada a varios cuadros solo conserva el primero de la lista.
Con la nómina en el orden del Excel, las 173 filas quedan reducidas a 133 antes del filtrado. Son 40 vínculos omitidos, no 40 personas distintas. El cuadro afectado puede variar con el orden que entregue la base.

### 2. Se borran códigos que no ofrece el catálogo actual

`js/cronograma.js:411–420` vacía los códigos que no reconoce. El controlador sí los recupera, pero la tabla los pierde antes de generar las opciones.
Hay 25 códigos históricos no seleccionables con el catálogo local. En las filas que sobrevivieron al filtrado se observaron 20 códigos distintos afectados, distribuidos en 412 celdas. Los restantes pueden estar en filas ya omitidas.
Esto es una incompatibilidad de la muestra con el catálogo/selector actual; no autoriza agregar siglas ni migrar códigos sin establecer las equivalencias.

| Objetivo del Excel | Filas esperadas | Filas visibles | Celdas con código en filas omitidas | Códigos vaciados en filas presentes |
|---|---:|---:|---:|---:|
| HOSPITAL NOTTI | 45 | 45 | 0 | 166 |
| HOSPITAL PERRUPATO | 36 | 35 | 9 | 76 |
| HOSPITAL SAPORITI | 22 | 19 | 62 | 14 |
| HOPITAL TAGARELLI | 11 | 11 | 0 | 29 |
| DINAF-ETI | 11 | 5 | 185 | 111 |
| SALA DE PALMIRA Y GUEMES | 3 | 2 | 31 | 0 |
| BOSQUE | 3 | 3 | 0 | 0 |
| METRAUX | 10 | 4 | 97 | 0 |
| ETI BERMEJO | 11 | 3 | 23 | 2 |
| HOGAR 8 MAIPU | 6 | 1 | 66 | 0 |
| CASA MC DONALDS | 2 | 1 | 31 | 14 |
| CASA 5,6 GODOY CRUZ | 6 | 2 | 37 | 0 |
| ETI TUPUNGATO | 7 | 2 | 15 | 0 |

## Comportamientos que necesitan una regla explícita

### Finales de mes con códigos especiales

Un único 10H o 12H el 31 de mayo se normaliza como F y genera F,D,D,N,N,F al comienzo de junio. 8H y 13H se normalizan como D. El algoritmo utiliza listas diferentes de las del selector.
Sin conocer el puesto/horario, no corresponde afirmar que todo código de duración es diurno o nocturno. Debe definirse su fase para continuar el ciclo. En la muestra, cinco filas terminan con 10H o 12H:

| Objetivo | Persona | Última fecha | Código |
|---|---|---|---|
| HOSPITAL PERRUPATO | Cornejo Alan | 2025-05-11 | 10H |
| HOSPITAL PERRUPATO | Godoy Gabriel | 2025-05-31 | 12H |
| HOSPITAL PERRUPATO | Ruiz Javier | 2025-05-31 | 10H |
| HOSPITAL PERRUPATO | Villarreal Hernan | 2025-05-31 | 12H |
| SALA DE PALMIRA Y GUEMES | Duscio Fabricio | 2025-05-31 | 12H |

### Historial incompleto y días sin asignación

41 filas con algún historial terminan antes del 31 de mayo; otras 2 no tienen códigos en todo el mes.
El algoritmo enlaza junio con el último registro disponible, aunque existan días vacíos hasta fin de mayo. Un historial D,D el 19 y 20 de mayo genera N,N el 1 y 2 de junio: no interpreta los once días vacíos intermedios.
Los vacíos del Excel significan que no trabaja. Todavía debe acordarse cómo esa ausencia afecta la fase del ciclo; este resultado no se presenta como una continuidad histórica validada.

### Generación desde cero

Al generar sin historial las 173 filas arrancan con D el primer día. No se escalona el personal para cubrir día/noche, y cada objetivo se genera de forma independiente. Esto verifica el patrón base, pero no asegura cobertura ni evita cruces entre objetivos.

## Alcance y límites

- PHP: se ejecutaron ControladorCronogramas y ModeloTurnos originales. Un adaptador en memoria respondió exclusivamente SELECT; no se incluyó conexion.php ni se abrió PDO.
- JavaScript: se ejecutó cronograma.js original en una VM, exponiendo funciones privadas solo en esa copia en memoria. Un DOM/jQuery mínimo permitió capturar HTML. No equivale a una prueba en navegador.
- No se probaron aún Select2, peticiones AJAX, autenticación, guardado real, rollback, mensajes ni totales visuales. El cálculo unitario de horas sí se comprobó.
- No se confirmó ni descartó una superposición horaria real: faltan horarios por puesto aplicables a los cuadros históricos.
- Los cuadros agrupados y nombres dudosos mantienen sus identidades de muestra. No se fusionaron empleados ni se insertaron registros.

## Reproducción y evidencia

Desde la raíz del proyecto:

```text
php tmp/cronogramas_mayo_2025/probar_armado.php
node tmp/cronogramas_mayo_2025/probar_tabla.cjs
```

- `pruebas_armado_php.json`: 48 casos, observaciones y los meses preparados en memoria.
- `pruebas_armado_js.json`: 47 casos, diferencias por celda y resultados por objetivo.
- Los fallos de producto en JavaScript se registran como `fallo` en el JSON; una excepción del ejecutor termina el proceso con error. No confundir código de salida del ejecutor con ausencia de fallos del producto.

SHA-256 del Excel intacto: `9d2709509431fd038e91a9bc23e66831822a8babb7ff32bfc0247d50fb9bd3d9`.
