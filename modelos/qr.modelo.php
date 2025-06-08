<?php

class QrModel
{

    public function guardarQrEnSesion($qr)
    {
        if (!isset($_SESSION['qr_codes']) || !is_array($_SESSION['qr_codes'])) {
            $_SESSION['qr_codes'] = [];
        }
        $_SESSION['qr_codes'][] = $qr;
    }
    public function guardarTodosEnBD()
    {
        try {
            $db = Conexion::conectar();
            $db->beginTransaction();

            $sql = "INSERT INTO escaneos (ronda_id, sector_id, vigilador_id, urlImg, image_path) VALUES (:ronda_id,:sector_id,:vigilador_id,:urlImg,:image_path)";
            $stmt = $db->prepare($sql);

            foreach ($_SESSION['qr_codes'] as $qr) {
                // parsear parámetros de la URL si lo necesitas, 
                // o bien guarda el idRonda/sector/vigilador desde sesión
                parse_str(parse_url($qr['data'], PHP_URL_QUERY), $params);

                $stmt->execute([
                    ':ronda_id'     => $params['ronda_id'],
                    ':sector_id'    => $params['sector_id'],
                    ':vigilador_id' => $params['vigilador_id'],
                    ':urlImg'       => $qr['data'],
                    ':imagePath'    => $qr['image']
                ]);
            }

            $db->commit();
            unset($_SESSION['qr_codes']);
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            return $e->getMessage();
        }
    }
}
