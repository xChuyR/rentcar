<?php
// backend/api/auth/request-reset.php
if ($method !== 'POST') fail('Método no permitido', 405);

$data  = body();
$email = strtolower(trim($data['email'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('Correo inválido');

$db = Database::get();

// Buscar usuario (respuesta siempre igual para no revelar emails)
$stmt = $db->prepare('SELECT id, nombre FROM usuarios WHERE email = ? AND activo = 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    // Invalidar códigos anteriores de reset
    $db->prepare('UPDATE codigos_mfa SET usado = 1 WHERE usuario_id = ? AND tipo = "reset"')
       ->execute([$user['id']]);

    // Generar token de reset (32 bytes aleatorios)
    $resetToken = bin2hex(random_bytes(32));
    $expiry     = date('Y-m-d H:i:s', time() + (RESET_EXPIRY_MIN * 60));

    // Guardar como código de tipo "reset" (usamos el campo 'codigo' para el token)
    $db->prepare(
        'INSERT INTO codigos_mfa (usuario_id, codigo, tipo, expira_en)
         VALUES (?, ?, "reset", ?)'
    )->execute([$user['id'], $resetToken, $expiry]);

    // Enviar correo
    Mailer::sendPasswordReset($email, $user['nombre'], $resetToken);
    log_action($user['id'], 'RESET_REQUESTED');
}

// Siempre devolver éxito (seguridad: no revelar si el correo existe)
ok(null, 'Si el correo está registrado, recibirás instrucciones en breve.');
