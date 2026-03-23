<?php
// backend/utils/jwt.php
require_once __DIR__ . '/../config/env.php';

class JWT {

    public static function encode(array $payload, int $expiryMinutes = 0): string {
        $header  = self::b64(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload['iat'] = time();
        if ($expiryMinutes > 0) {
            $payload['exp'] = time() + ($expiryMinutes * 60);
        }
        $body = self::b64($payload);
        $sig  = self::sign("{$header}.{$body}");
        return "{$header}.{$body}.{$sig}";
    }

    public static function decode(string $token): array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) throw new RuntimeException('Invalid token');

        [$header, $body, $sig] = $parts;

        // Verificar firma
        $expected = self::sign("{$header}.{$body}");
        if (!hash_equals($expected, $sig)) {
            throw new RuntimeException('Invalid signature');
        }

        $payload = json_decode(self::b64d($body), true);
        if (!$payload) throw new RuntimeException('Invalid payload');

        // Verificar expiración
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new RuntimeException('Token expired');
        }

        return $payload;
    }

    private static function b64(array $data): string {
        return rtrim(strtr(base64_encode(json_encode($data)), '+/', '-_'), '=');
    }

    private static function b64d(string $data): string {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    private static function sign(string $data): string {
        return rtrim(strtr(
            base64_encode(hash_hmac('sha256', $data, JWT_SECRET, true)),
            '+/', '-_'
        ), '=');
    }
}
