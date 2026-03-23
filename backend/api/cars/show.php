<?php
// backend/api/cars/show.php
if ($method !== 'GET') fail('Método no permitido', 405);

$carId = (int) ($id ?? 0);
if (!$carId) fail('ID inválido');

$db   = Database::get();
$stmt = $db->prepare('SELECT * FROM autos WHERE id = ?');
$stmt->execute([$carId]);
$car  = $stmt->fetch();

if (!$car) fail('Vehículo no encontrado', 404);

$car['caracteristicas'] = json_decode($car['caracteristicas'] ?? '[]', true);

ok($car);
