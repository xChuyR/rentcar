<?php
// backend/api/contact/store.php
if ($method !== 'POST') fail('Método no permitido', 405);

$data    = body();
$nombre  = trim($data['nombre']  ?? '');
$email   = strtolower(trim($data['email'] ?? ''));
$asunto  = trim($data['asunto']  ?? '');
$mensaje = trim($data['mensaje'] ?? '');

if (!$nombre || !$email || !$mensaje) fail('Nombre, correo y mensaje son requeridos');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail('Correo inválido');
if (strlen($mensaje) < 10) fail('El mensaje es demasiado corto');

// Detectar usuario logueado opcional
$userId = null;
$auth   = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (str_starts_with($auth, 'Bearer ')) {
    try {
        $p      = JWT::decode(substr($auth, 7));
        $userId = $p['user_id'] ?? null;
    } catch (Throwable) {}
}

$db = Database::get();
$db->prepare(
    'INSERT INTO mensaje_contacto (usuario_id, nombre, email, asunto, mensaje, ip)
     VALUES (?, ?, ?, ?, ?, ?)'
)->execute([$userId, $nombre, $email, $asunto, $mensaje, ip()]);

log_action($userId, 'CONTACT_FORM', ['email' => $email]);
ok(null, 'Mensaje enviado correctamente');
