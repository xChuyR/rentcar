<?php
// backend/api/cars/update.php
if ($method !== 'PUT') fail('Método no permitido', 405);

$payload = requireAuth(['admin']);
$carId   = (int) ($id ?? 0);
if (!$carId) fail('ID inválido');

$db   = Database::get();
$stmt = $db->prepare('SELECT id FROM autos WHERE id = ?');
$stmt->execute([$carId]);
if (!$stmt->fetch()) fail('Vehículo no encontrado', 404);

$isMultipart = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data');

if ($isMultipart) {
    $fields = $_POST;
} else {
    $fields = body();
}

// Construir SET dinámico solo con campos enviados
$allowed = ['nombre','marca','modelo','`año`','tipo','precio_dia','descripcion',
            'caracteristicas','disponible','pasajeros','transmision','motor'];
$set    = [];
$params = [];

foreach ($allowed as $col) {
    $formKey = ($col === '`año`') ? 'anio' : $col;
    if (isset($fields[$formKey])) {
        $val = $fields[$formKey];
        if ($col === 'caracteristicas' && is_array($val)) {
            $val = json_encode($val);
        }
        if ($col === '`año`') $val = (int) $val;
        if ($col === 'precio_dia') $val = (float) $val;
        if ($col === 'pasajeros') $val = (int) $val;
        if ($col === 'disponible') {
            $val = ($val === 'true' || $val === '1' || $val === 1 || $val === true) ? 1 : 0;
        }
        $set[]    = "{$col} = ?";
        $params[] = $val;
    }
}

// Manejar nueva imagen si se subió
if ($isMultipart && isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $file    = $_FILES['imagen'];
    $finfo   = new finfo(FILEINFO_MIME_TYPE);
    $mime    = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ALLOWED_IMG_TYPES, true)) fail('Tipo de imagen no permitido');

    $ext      = match($mime) { 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', default => 'jpg' };
    $filename = 'car_' . uniqid() . '.' . $ext;
    $destPath = UPLOAD_DIR . $filename;
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    if (move_uploaded_file($file['tmp_name'], $destPath)) {
        $set[]    = '`imagen_url` = ?';
        $params[] = UPLOAD_URL . $filename;
    }
}

if (empty($set)) fail('Nada que actualizar');

$params[] = $carId;
$db->prepare('UPDATE autos SET ' . implode(', ', $set) . ' WHERE id = ?')->execute($params);

log_action($payload['user_id'], 'CAR_UPDATED', ['car_id' => $carId]);
ok(['id' => $carId], 'Vehículo actualizado');
