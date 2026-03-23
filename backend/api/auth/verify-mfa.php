<?php
// backend/api/auth/verify-mfa.php
// Paso 2: verifica el código MFA y emite el token de sesión final.
if ($method !== 'POST') fail('Método no permitido', 405);

// Verificar token temporal
$tempPayload = requireTempToken();
$userId      = (int) $tempPayload['user_id'];

$data = body();
$code = trim($data['code'] ?? '');

if (strlen($code) !== 6 || !ctype_digit($code)) {
    fail('Código inválido');
}

$db = Database::get();

// Buscar código válido (no expirado, no usado, para este usuario)
$stmt = $db->prepare(
    'SELECT * FROM codigos_mfa
     WHERE usuario_id = ? AND tipo = "login" AND usado = 0 AND expira_en > NOW()
     ORDER BY creado_en DESC LIMIT 1'
);
$stmt->execute([$userId]);
$mfa = $stmt->fetch();

if (!$mfa) {
    fail('Código expirado o ya utilizado. Inicia sesión nuevamente.', 401);
}

// Incrementar intentos
$db->prepare('UPDATE codigos_mfa SET intentos = intentos + 1 WHERE id = ?')
   ->execute([$mfa['id']]);

// Verificar máximo de intentos
if ($mfa['intentos'] >= MFA_MAX_ATTEMPTS) {
    $db->prepare('UPDATE codigos_mfa SET usado = 1 WHERE id = ?')->execute([$mfa['id']]);
    log_action($userId, 'MFA_MAX_ATTEMPTS');
    fail('Demasiados intentos. Inicia sesión nuevamente.', 429);
}

if ($mfa['codigo'] !== $code) {
    log_action($userId, 'MFA_FAIL', ['attempt' => $mfa['intentos']]);
    fail('Código incorrecto', 401);
}

// Marcar código como usado
$db->prepare('UPDATE codigos_mfa SET usado = 1 WHERE id = ?')->execute([$mfa['id']]);

// Obtener datos del usuario
$stmt = $db->prepare('SELECT id, nombre, apellido, email, rol, avatar_url FROM usuarios WHERE id = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Generar token de sesión final
$expiryHours = JWT_EXPIRY_HOURS;
$token = JWT::encode([
    'user_id'  => $user['id'],
    'email'    => $user['email'],
    'rol'      => $user['rol'],
    'nombre'   => $user['nombre'],
], $expiryHours * 60);

// Persistir sesión en DB
$tokenHash = hash('sha256', $token);
$expiry    = date('Y-m-d H:i:s', time() + ($expiryHours * 3600));
$db->prepare(
    'INSERT INTO sesiones (usuario_id, token_hash, ip, user_agent, expira_en)
     VALUES (?, ?, ?, ?, ?)'
)->execute([
    $user['id'], $tokenHash, ip(),
    substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
    $expiry
]);

log_action($user['id'], 'LOGIN_OK');

ok([
    'token' => $token,
    'user'  => [
        'id'         => $user['id'],
        'nombre'     => $user['nombre'],
        'apellido'   => $user['apellido'],
        'email'      => $user['email'],
        'rol'        => $user['rol'],
        'avatar_url' => $user['avatar_url'],
    ],
    'expires_at' => $expiry,
], 'Autenticado correctamente');
