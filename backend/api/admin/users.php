<?php
// backend/api/admin/users.php
$payload = requireAuth(['admin']);
$db      = Database::get();

if ($method === 'GET') {
    $users = $db->query(
        'SELECT id, nombre, apellido, email, rol, telefono, activo, email_verificado, creado_en
         FROM usuarios ORDER BY creado_en DESC'
    )->fetchAll();
    ok($users);
}

if ($method === 'PUT') {
    $userId  = (int) ($id ?? 0);
    if (!$userId) fail('ID requerido');
    $data    = body();

    $allowed = ['nombre','apellido','rol','activo','email_verificado','telefono'];
    $set     = []; $params = [];

    foreach ($allowed as $col) {
        if (array_key_exists($col, $data)) {
            $set[]    = "`{$col}` = ?";
            $params[] = $data[$col];
        }
    }

    if (empty($set)) fail('Nada que actualizar');
    $params[] = $userId;
    $db->prepare('UPDATE usuarios SET ' . implode(', ', $set) . ' WHERE id = ?')->execute($params);

    log_action($payload['user_id'], 'USER_UPDATED', ['target_user' => $userId]);
    ok(null, 'Usuario actualizado');
}

if ($method === 'DELETE') {
    $userId = (int) ($id ?? 0);
    if (!$userId) fail('ID requerido');
    if ($userId === $payload['user_id']) fail('No puedes eliminarte a ti mismo');

    $db->prepare('DELETE FROM usuarios WHERE id = ?')->execute([$userId]);
    log_action($payload['user_id'], 'USER_DELETED', ['target_user' => $userId]);
    ok(null, 'Usuario eliminado');
}

fail('Método no permitido', 405);
