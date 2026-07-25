-- Ejecutar una sola vez sobre una base existente.
-- spec.sql ya contiene estas columnas para instalaciones nuevas.

ALTER TABLE `marcaciones_servicio`
  ADD COLUMN `operacion_id` varchar(64) DEFAULT NULL AFTER `created_at`,
  ADD UNIQUE KEY `uq_marcaciones_operacion` (`operacion_id`);

ALTER TABLE `reporte_hombre_vivo`
  ADD COLUMN `operacion_id` varchar(64) DEFAULT NULL AFTER `demora`,
  ADD UNIQUE KEY `uq_hvivo_operacion` (`operacion_id`);

ALTER TABLE `escaneos`
  ADD COLUMN `operacion_id` varchar(64) DEFAULT NULL AFTER `fecha_hora`,
  ADD UNIQUE KEY `uq_escaneos_operacion` (`operacion_id`);
