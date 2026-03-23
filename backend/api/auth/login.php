<?php
// backend/api/auth/login.php
// Paso 1: valida email+password, genera código MFA y lo envía al correo.
// Devuelve un token temporal para usar en /verify-mfa.
if ($method !== 'POST') fail('Método no permitido', 405);

$data     = body();
$email    = strtolower(trim($data['email']    ?? ''));
$password = $data['password'] ?? '';

if (!$email || !$password) fail('Correo y contraseña requeridos');

$db = Database::get();

// Buscar usuario activo
$stmt = $db->prepare('SELECT * FROM usuarios WHERE email = ? AND activo = 1');
$stmt->execute([$email]);
$user = $stmt->fetch();

// Respuesta deliberadamente genérica para no revelar si el email existe
if (!$user || !password_verify($password, $user['password_hash'])) {
    log_action(null, 'LOGIN_FAIL', ['email' => $email]);
    fail('Credenciales incorrectas', 401);
}

// Invalidar códigos MFA anteriores de login para este usuario
$db->prepare('UPDATE codigos_mfa SET usado = 1 WHERE usuario_id = ? AND tipo = "login"')
   ->execute([$user['id']]);

// Generar código MFA de 6 dígitos
$code    = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$expiry  = date('Y-m-d H:i:s', time() + (MFA_CODE_EXPIRY_MIN * 60));

$stmt = $db->prepare(
    'INSERT INTO codigos_mfa (usuario_id, codigo, tipo, expira_en)
     VALUES (?, ?, "login", ?)'
);
$stmt->execute([$user['id'], $code, $expiry]);

// Enviar código por correo
$sent = Mailer::sendMFACode($user['email'], $user['nombre'], $code);

log_action($user['id'], 'MFA_SENT', ['email' => $user['email'], 'sent' => $sent]);

// Token temporal pre-MFA (expira igual que el código)
$tempToken = JWT::encode([
    'type'    => 'pre_mfa',
    'user_id' => $user['id'],
    'email'   => $user['email'],
], MFA_CODE_EXPIRY_MIN);

ok([
    'temp_token' => $tempToken,
    'email_hint' => preg_replace('/(.{2}).+(@.+)/', '$1•••$2', $user['email']),
    'mfa_sent'   => $sent,
], 'Código MFA enviado al correo');
