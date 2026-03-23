<?php
// backend/api/cars/delete.php
if ($method !== 'DELETE') fail('Método no permitido', 405);

$payload = requireAuth(['admin']);
$carId   = (int) ($id ?? 0);
if (!$carId) fail('ID inválido');

$db   = Database::get();
$stmt = $db->prepare('SELECT id, nombre FROM autos WHERE id = ?');
$stmt->execute([$carId]);
$car  = $stmt->fetch();
if (!$car) fail('Vehículo no encontrado', 404);

// Eliminar de carritos primero (FK)
$db->prepare('DELETE FROM carrito WHERE auto_id = ?')->execute([$carId]);
$db->prepare('DELETE FROM autos WHERE id = ?')->execute([$carId]);

log_action($payload['user_id'], 'CAR_DELETED', ['car_id' => $carId, 'nombre' => $car['nombre']]);
ok(null, 'Vehículo eliminado');
