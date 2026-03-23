<?php
// backend/api/auth/reset-password.php
if ($method !== 'POST') fail('Método no permitido', 405);

$data        = body();
$token       = trim($data['token']    ?? '');
$newPassword = $data['password']      ?? '';
$confirm     = $data['password_confirm'] ?? '';

if (!$token) fail('Token requerido');
if (!$newPassword) fail('Nueva contraseña requerida');
if ($newPassword !== $confirm) fail('Las contraseñas no coinciden');
if (strlen($newPassword) < 8) fail('Mínimo 8 caracteres');
if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
    fail('La contraseña debe contener al menos una mayúscula y un número');
}

$db = Database::get();

// Buscar token válido
$stmt = $db->prepare(
    'SELECT * FROM codigos_mfa
     WHERE codigo = ? AND tipo = "reset" AND usado = 0 AND expira_en > NOW()
     LIMIT 1'
);
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    fail('El enlace es inválido o ya expiró', 400);
}

// Actualizar contraseña
$hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
$db->prepare('UPDATE usuarios SET password_hash = ? WHERE id = ?')
   ->execute([$hash, $reset['usuario_id']]);

// Invalidar token y todas las sesiones activas
$db->prepare('UPDATE codigos_mfa SET usado = 1 WHERE id = ?')->execute([$reset['id']]);
$db->prepare('UPDATE sesiones SET cerrado_en = NOW() WHERE usuario_id = ? AND cerrado_en IS NULL')
   ->execute([$reset['usuario_id']]);

log_action($reset['usuario_id'], 'PASSWORD_RESET');

ok(null, 'Contraseña actualizada correctamente. Ya puedes iniciar sesión.');
