<?php
require_once __DIR__ . '/conexion.php';

class ModeloPush
{
    public static function mdlGuardarSuscripcion(int $usuarioId, array $suscripcion)
    {
        $endpoint = trim((string)($suscripcion['endpoint'] ?? ''));
        $keys = $suscripcion['keys'] ?? [];
        $p256dh = trim((string)($keys['p256dh'] ?? ''));
        $auth = trim((string)($keys['auth'] ?? ''));
        $contentEncoding = trim((string)($suscripcion['contentEncoding'] ?? ''));
        $userAgent = trim((string)($suscripcion['userAgent'] ?? ($_SERVER['HTTP_USER_AGENT'] ?? '')));

        if ($usuarioId <= 0 || $endpoint === '' || $p256dh === '' || $auth === '') {
            return 'Suscripcion invalida';
        }

        $sql = "INSERT INTO push_subscriptions
                    (usuario_id, endpoint, p256dh, auth, content_encoding, user_agent, subscription_json)
                VALUES
                    (:usuario_id, :endpoint, :p256dh, :auth, :content_encoding, :user_agent, :subscription_json)
                ON DUPLICATE KEY UPDATE
                    usuario_id = VALUES(usuario_id),
                    p256dh = VALUES(p256dh),
                    auth = VALUES(auth),
                    content_encoding = VALUES(content_encoding),
                    user_agent = VALUES(user_agent),
                    subscription_json = VALUES(subscription_json),
                    updated_at = CURRENT_TIMESTAMP";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->bindValue(':usuario_id', $usuarioId, PDO::PARAM_INT);
        $stmt->bindValue(':endpoint', $endpoint, PDO::PARAM_STR);
        $stmt->bindValue(':p256dh', $p256dh, PDO::PARAM_STR);
        $stmt->bindValue(':auth', $auth, PDO::PARAM_STR);
        $stmt->bindValue(':content_encoding', $contentEncoding ?: 'aes128gcm', PDO::PARAM_STR);
        $stmt->bindValue(':user_agent', $userAgent, PDO::PARAM_STR);
        $stmt->bindValue(':subscription_json', json_encode($suscripcion, JSON_UNESCAPED_UNICODE), PDO::PARAM_STR);

        return $stmt->execute() ? 'ok' : ($stmt->errorInfo()[2] ?? 'No se pudo guardar la suscripcion');
    }

    public static function mdlEliminarSuscripcionPorEndpoint(string $endpoint): bool
    {
        $endpoint = trim($endpoint);
        if ($endpoint === '') {
            return false;
        }

        $stmt = Conexion::conectar()->prepare("DELETE FROM push_subscriptions WHERE endpoint = ?");
        return $stmt->execute([$endpoint]);
    }

    public static function mdlObtenerSuscripcionesPorUsuarios(array $usuarioIds): array
    {
        $usuarioIds = array_values(array_unique(array_filter(array_map('intval', $usuarioIds))));
        if (!$usuarioIds) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($usuarioIds), '?'));
        $sql = "SELECT id, usuario_id, endpoint, p256dh, auth, content_encoding, user_agent
                FROM push_subscriptions
                WHERE usuario_id IN ($placeholders)";

        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute($usuarioIds);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public static function enviarPushAUsuarios(array $usuarioIds): int
    {
        $suscripciones = self::mdlObtenerSuscripcionesPorUsuarios($usuarioIds);
        if (!$suscripciones) {
            return 0;
        }

        $enviadas = 0;
        $paraEliminar = [];

        foreach ($suscripciones as $suscripcion) {
            $endpoint = (string)($suscripcion['endpoint'] ?? '');
            if ($endpoint === '') {
                continue;
            }

            try {
                $aud = self::obtenerAudiencia($endpoint);
                $jwt = self::generarJwtVapid($aud);
                $headers = [
                    'TTL: 60',
                    'Content-Type: application/octet-stream',
                    'Content-Length: 0',
                    'Authorization: vapid t=' . $jwt . ', k=' . PUSH_VAPID_PUBLIC_KEY,
                ];

                $ch = curl_init($endpoint);
                curl_setopt_array($ch, [
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => '',
                    CURLOPT_HTTPHEADER => $headers,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HEADER => true,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_CONNECTTIMEOUT => 5,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                ]);

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $curlError = curl_error($ch);
                curl_close($ch);

                if ($response === false || $curlError) {
                    error_log('Push: error enviando a ' . $endpoint . ' => ' . $curlError);
                    continue;
                }

                if (in_array($httpCode, [404, 410], true)) {
                    $paraEliminar[] = $endpoint;
                    continue;
                }

                if ($httpCode >= 200 && $httpCode < 300) {
                    $enviadas++;
                }
            } catch (Throwable $e) {
                error_log('Push: ' . $e->getMessage());
            }
        }

        foreach ($paraEliminar as $endpoint) {
            self::mdlEliminarSuscripcionPorEndpoint($endpoint);
        }

        return $enviadas;
    }

    private static function obtenerAudiencia(string $endpoint): string
    {
        $partes = parse_url($endpoint);
        if (!$partes || empty($partes['scheme']) || empty($partes['host'])) {
            throw new RuntimeException('Endpoint push invalido');
        }

        $aud = $partes['scheme'] . '://' . $partes['host'];
        if (!empty($partes['port'])) {
            $aud .= ':' . $partes['port'];
        }

        return $aud;
    }

    private static function generarJwtVapid(string $audiencia): string
    {
        $header = ['typ' => 'JWT', 'alg' => 'ES256'];
        $payload = [
            'aud' => $audiencia,
            'exp' => time() + 12 * 3600,
            'sub' => PUSH_VAPID_SUBJECT,
        ];

        $base = self::base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES)) . '.' .
                self::base64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES));

        $clave = openssl_pkey_get_private(PUSH_VAPID_PRIVATE_KEY_PEM);
        if (!$clave) {
            throw new RuntimeException('No se pudo cargar la clave privada VAPID');
        }

        $firmaDer = '';
        if (!openssl_sign($base, $firmaDer, $clave, OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('No se pudo firmar el JWT VAPID');
        }

        $firmaJose = self::derToJoseSignature($firmaDer, 64);
        return $base . '.' . self::base64UrlEncode($firmaJose);
    }

    private static function derToJoseSignature(string $der, int $length = 64): string
    {
        $offset = 0;
        if (ord($der[$offset++]) !== 0x30) {
            throw new RuntimeException('Firma DER invalida');
        }
        self::leerLongitudDer($der, $offset);

        if (ord($der[$offset++]) !== 0x02) {
            throw new RuntimeException('Firma DER invalida');
        }
        $rLength = self::leerLongitudDer($der, $offset);
        $r = substr($der, $offset, $rLength);
        $offset += $rLength;

        if (ord($der[$offset++]) !== 0x02) {
            throw new RuntimeException('Firma DER invalida');
        }
        $sLength = self::leerLongitudDer($der, $offset);
        $s = substr($der, $offset, $sLength);

        $r = ltrim($r, "\x00");
        $s = ltrim($s, "\x00");
        $r = str_pad($r, $length / 2, "\x00", STR_PAD_LEFT);
        $s = str_pad($s, $length / 2, "\x00", STR_PAD_LEFT);

        return $r . $s;
    }

    private static function leerLongitudDer(string $der, int &$offset): int
    {
        $len = ord($der[$offset++]);
        if (($len & 0x80) === 0) {
            return $len;
        }

        $bytes = $len & 0x7F;
        $out = 0;
        for ($i = 0; $i < $bytes; $i++) {
            $out = ($out << 8) | ord($der[$offset++]);
        }
        return $out;
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
