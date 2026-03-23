<?php
// backend/api/auth/me.php
if ($method !== 'GET') fail('Método no permitido', 405);

$payload = requireAuth();

$db   = Database::get();
$stmt = $db->prepare('SELECT id, nombre, apellido, email, rol, telefono, avatar_url, creado_en FROM usuarios WHERE id = ?');
$stmt->execute([$payload['user_id']]);
$user = $stmt->fetch();

if (!$user) fail('Usuario no encontrado', 404);

ok($user);
