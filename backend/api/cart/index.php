<?php
// backend/api/cart/index.php
$payload = requireAuth();
$userId  = $payload['user_id'];
$db      = Database::get();

// ── GET /api/cart ─────────────────────────────────────────
if ($method === 'GET') {
    $stmt = $db->prepare(
        'SELECT c.id, c.auto_id, c.dias_renta, c.fecha_inicio, c.fecha_fin,
                a.nombre, a.tipo, a.precio_dia, a.imagen_url,
                (a.precio_dia * c.dias_renta) AS subtotal
         FROM carrito c
         JOIN autos a ON a.id = c.auto_id
         WHERE c.usuario_id = ?'
    );
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll();
    $total = array_sum(array_column($items, 'subtotal'));
    ok(['items' => $items, 'total' => $total, 'count' => count($items)]);
}

// ── POST /api/cart ────────────────────────────────────────
if ($method === 'POST') {
    $data       = body();
    $autoId     = (int)($data['auto_id']    ?? 0);
    $diasRenta  = max(1, (int)($data['dias_renta'] ?? 1));
    $fechaInicio= $data['fecha_inicio'] ?? null;
    $fechaFin   = $data['fecha_fin']   ?? null;

    if (!$autoId) fail('auto_id requerido');

    // Verificar que el auto exista y esté disponible
    $stmt = $db->prepare('SELECT id FROM autos WHERE id = ? AND disponible = 1');
    $stmt->execute([$autoId]);
    if (!$stmt->fetch()) fail('Vehículo no disponible', 404);

    // INSERT OR UPDATE
    $stmt = $db->prepare(
        'INSERT INTO carrito (usuario_id, auto_id, dias_renta, fecha_inicio, fecha_fin)
         VALUES (?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
           dias_renta   = VALUES(dias_renta),
           fecha_inicio = VALUES(fecha_inicio),
           fecha_fin    = VALUES(fecha_fin)'
    );
    $stmt->execute([$userId, $autoId, $diasRenta, $fechaInicio, $fechaFin]);
    ok(['auto_id' => $autoId], 'Agregado al carrito', 201);
}

// ── DELETE /api/cart ──────────────────────────────────────
if ($method === 'DELETE') {
    $data   = body();
    $autoId = (int)($data['auto_id'] ?? 0);

    if ($autoId) {
        $db->prepare('DELETE FROM carrito WHERE usuario_id = ? AND auto_id = ?')
           ->execute([$userId, $autoId]);
        ok(null, 'Eliminado del carrito');
    }

    // Vaciar todo el carrito
    $db->prepare('DELETE FROM carrito WHERE usuario_id = ?')->execute([$userId]);
    ok(null, 'Carrito vaciado');
}

fail('Método no permitido', 405);
