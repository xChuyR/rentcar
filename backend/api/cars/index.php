<?php
// backend/api/cars/index.php
if ($method !== 'GET') fail('Método no permitido', 405);

$db = Database::get();

// Filtros opcionales por query string
$tipo       = $_GET['tipo']  ?? '';
$search     = $_GET['q']     ?? '';
$minPrice   = $_GET['min']   ?? '';
$maxPrice   = $_GET['max']   ?? '';
$disponible = $_GET['disponible'] ?? '1';

$where  = ['1 = 1'];
$params = [];

if ($tipo) {
    $where[]  = 'tipo = ?';
    $params[] = $tipo;
}
if ($disponible !== '') {
    $where[]  = 'disponible = ?';
    $params[] = (int) $disponible;
}
if ($minPrice !== '') {
    $where[]  = 'precio_dia >= ?';
    $params[] = (float) $minPrice;
}
if ($maxPrice !== '') {
    $where[]  = 'precio_dia <= ?';
    $params[] = (float) $maxPrice;
}
if ($search) {
    $where[]  = '(nombre LIKE ? OR descripcion LIKE ? OR marca LIKE ? OR modelo LIKE ? OR tipo LIKE ?)';
    $like     = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql  = 'SELECT * FROM autos WHERE ' . implode(' AND ', $where) . ' ORDER BY creado_en DESC';
$stmt = $db->prepare($sql);
$stmt->execute($params);
$cars = $stmt->fetchAll();

// Decodificar JSON de características
foreach ($cars as &$car) {
    $car['caracteristicas'] = json_decode($car['caracteristicas'] ?? '[]', true);
}

ok($cars);
