<?php
// backend/middleware/auth.php
require_once __DIR__ . '/../utils/jwt.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../config/db.php';

/**
 * Verifica el JWT del header Authorization.
 * Devuelve el payload si es válido, o termina la respuesta con 401.
 */
function requireAuth(array $allowedRoles = []): array {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

    // Intentar obtener de apache_request_headers si no está en $_SERVER
    if (!$header && function_exists('apache_request_headers')) {
        $allHeaders = apache_request_headers();
        $header = $allHeaders['Authorization'] ?? $allHeaders['authorization'] ?? '';
    }

    if (!str_starts_with($header, 'Bearer ')) {
        fail('No autenticado', 401);
    }

    $token = substr($header, 7);

    try {
        $payload = JWT::decode($token);
    } catch (RuntimeException $e) {
        fail($e->getMessage(), 401);
    }

    // Verificar que la sesión esté activa en DB (no revocada)
    $tokenHash = hash('sha256', $token);
    $db        = Database::get();
    $stmt      = $db->prepare(
        'SELECT id FROM sesiones
         WHERE token_hash = ? AND cerrado_en IS NULL AND expira_en > NOW()'
    );
    $stmt->execute([$tokenHash]);
    if (!$stmt->fetch()) {
        fail('Sesión inválida o expirada', 401);
    }

    // Verificar rol si se especificaron roles permitidos
    if (!empty($allowedRoles) && !in_array($payload['rol'] ?? '', $allowedRoles, true)) {
        fail('No autorizado', 403);
    }

    return $payload;
}

/**
 * Solo verifica el token temporal pre-MFA (no registrado en sesiones).
 */
function requireTempToken(): array {
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

    // Intentar obtener de apache_request_headers si no está en $_SERVER
    if (!$header && function_exists('apache_request_headers')) {
        $allHeaders = apache_request_headers();
        $header = $allHeaders['Authorization'] ?? $allHeaders['authorization'] ?? '';
    }

    if (!str_starts_with($header, 'Bearer ')) {
        fail('Token temporal requerido', 401);
    }

    $token = substr($header, 7);
    try {
        $payload = JWT::decode($token);
    } catch (RuntimeException $e) {
        fail($e->getMessage(), 401);
    }

    if (($payload['type'] ?? '') !== 'pre_mfa') {
        fail('Token no es de tipo pre-MFA', 401);
    }

    return $payload;
}
