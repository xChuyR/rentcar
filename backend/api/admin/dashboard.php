<?php
// backend/api/admin/dashboard.php
if ($method !== 'GET') fail('Método no permitido', 405);
requireAuth(['admin']);

$db = Database::get();

$stats = [
    'usuarios'        => (int) $db->query('SELECT COUNT(*) FROM usuarios')->fetchColumn(),
    'autos'           => (int) $db->query('SELECT COUNT(*) FROM autos')->fetchColumn(),
    'autos_disponibles'=> (int) $db->query('SELECT COUNT(*) FROM autos WHERE disponible=1')->fetchColumn(),
    'mensajes_nuevos' => (int) $db->query('SELECT COUNT(*) FROM mensaje_contacto WHERE leido=0')->fetchColumn(),
    'sesiones_activas'=> (int) $db->query('SELECT COUNT(*) FROM sesiones WHERE cerrado_en IS NULL AND expira_en > NOW()')->fetchColumn(),
];

// Últimos mensajes sin leer
$mensajes = $db->query(
    'SELECT id, nombre, email, LEFT(mensaje,80) as preview, creado_en
     FROM mensaje_contacto WHERE leido = 0 ORDER BY creado_en DESC LIMIT 5'
)->fetchAll();

// Últimos logs
$logs = $db->query(
    'SELECT l.id, l.accion, l.ip, l.creado_en,
            COALESCE(u.nombre, "Anónimo") AS usuario
     FROM logs l LEFT JOIN usuarios u ON u.id = l.usuario_id
     ORDER BY l.creado_en DESC LIMIT 20'
)->fetchAll();

// Todos los mensajes de contacto (para panel admin)
$allMensajes = $db->query(
    'SELECT mc.*, COALESCE(u.nombre, "—") AS usuario_nombre
     FROM mensaje_contacto mc LEFT JOIN usuarios u ON u.id = mc.usuario_id
     ORDER BY mc.creado_en DESC LIMIT 50'
)->fetchAll();

ok(['stats' => $stats, 'mensajes_nuevos' => $mensajes, 'logs' => $logs, 'mensajes' => $allMensajes]);
