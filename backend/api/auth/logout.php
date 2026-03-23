<?php
// backend/api/auth/logout.php
if ($method !== 'POST') fail('Método no permitido', 405);

$payload = requireAuth();

// Revocar sesión actual
$header    = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$token     = substr($header, 7);
$tokenHash = hash('sha256', $token);

Database::get()->prepare(
    'UPDATE sesiones SET cerrado_en = NOW() WHERE token_hash = ?'
)->execute([$tokenHash]);

log_action($payload['user_id'], 'LOGOUT');
ok(null, 'Sesión cerrada');
