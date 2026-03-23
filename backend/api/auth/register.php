<?php
// backend/api/auth/register.php
if ($method !== 'POST') fail('Método no permitido', 405);

$data     = body();
$nombre   = trim($data['nombre']   ?? '');
$apellido = trim($data['apellido'] ?? '');
$email    = strtolower(trim($data['email']    ?? ''));
$password = $data['password'] ?? '';
$telefono = trim($data['telefono'] ?? '');

// Validar
if (!$nombre || !$apellido || !$email || !$password) {
    fail('Todos los campos son requeridos');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Correo inválido');
}
if (strlen($password) < 8) {
    fail('La contraseña debe tener al menos 8 caracteres');
}
if (!preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
    fail('La contraseña debe tener al menos una mayúscula y un número');
}

$db = Database::get();

// Verificar que el email no exista
$stmt = $db->prepare('SELECT id FROM usuarios WHERE email = ?');
$stmt->execute([$email]);
if ($stmt->fetch()) {
    fail('Este correo ya está registrado', 409);
}

// Crear usuario
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]);
$stmt = $db->prepare(
    'INSERT INTO usuarios (nombre, apellido, email, password_hash, rol, telefono)
     VALUES (?, ?, ?, ?, "cliente", ?)'
);
$stmt->execute([$nombre, $apellido, $email, $hash, $telefono]);
$userId = (int) $db->lastInsertId();

// Enviar correo de bienvenida (no bloquea si falla)
try {
    Mailer::sendWelcome($email, $nombre);
} catch (Throwable) {}

log_action($userId, 'REGISTER', ['email' => $email]);

ok(['id' => $userId, 'email' => $email], 'Cuenta creada exitosamente', 201);
