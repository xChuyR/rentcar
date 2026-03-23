<?php
// backend/api/cars/store.php
if ($method !== 'POST') fail('Método no permitido', 405);

$payload = requireAuth(['admin']);

// Soporta multipart/form-data (con imagen) o JSON
$isMultipart = str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'multipart/form-data');

if ($isMultipart) {
    $nombre         = trim($_POST['nombre']         ?? '');
    $marca          = trim($_POST['marca']          ?? '');
    $modelo         = trim($_POST['modelo']         ?? '');
    $anio           = (int)($_POST['anio']          ?? 0);
    $tipo           = $_POST['tipo']                ?? '';
    $precio_dia     = (float)($_POST['precio_dia']  ?? 0);
    $descripcion    = trim($_POST['descripcion']    ?? '');
    $caracteristicas= $_POST['caracteristicas']     ?? '[]';
    $pasajeros      = (int)($_POST['pasajeros']     ?? 5);
    $transmision    = $_POST['transmision']         ?? 'Automático';
    $motor          = trim($_POST['motor']          ?? '');
    $disponible     = ($_POST['disponible'] ?? '1');
    $disponible     = ($disponible === 'true' || $disponible === '1' || $disponible === 1) ? 1 : 0;
} else {
    $data           = body();
    $nombre         = trim($data['nombre']          ?? '');
    $marca          = trim($data['marca']           ?? '');
    $modelo         = trim($data['modelo']          ?? '');
    $anio           = (int)($data['anio']           ?? 0);
    $tipo           = $data['tipo']                 ?? '';
    $precio_dia     = (float)($data['precio_dia']   ?? 0);
    $descripcion    = trim($data['descripcion']     ?? '');
    $caracteristicas= is_array($data['caracteristicas'] ?? null)
                        ? json_encode($data['caracteristicas'])
                        : ($data['caracteristicas'] ?? '[]');
    $pasajeros      = (int)($data['pasajeros']      ?? 5);
    $transmision    = $data['transmision']          ?? 'Automático';
    $motor          = trim($data['motor']           ?? '');
    $disponible     = ($data['disponible'] ?? 1);
    $disponible     = ($disponible === 'true' || $disponible === '1' || $disponible === 1 || $disponible === true) ? 1 : 0;
}

// Validar campos obligatorios
if (!$nombre || !$marca || !$modelo || !$anio || !$tipo || !$precio_dia) {
    fail('Nombre, marca, modelo, año, tipo y precio son requeridos');
}

$tiposValidos = ['SUV','Sedan','Deportivo','Pickup','Eléctrico','Minivan'];
if (!in_array($tipo, $tiposValidos, true)) fail('Tipo de vehículo inválido');
if ($precio_dia <= 0) fail('El precio debe ser mayor a 0');
if ($anio < 2000 || $anio > (int)date('Y') + 1) fail('Año inválido');

// Manejar subida de imagen
$imagenUrl = null;
if ($isMultipart && isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $file    = $_FILES['imagen'];
    $maxSize = MAX_FILE_SIZE_MB * 1024 * 1024;

    if ($file['size'] > $maxSize) fail('La imagen no puede superar ' . MAX_FILE_SIZE_MB . 'MB');

    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!in_array($mimeType, ALLOWED_IMG_TYPES, true)) {
        fail('Solo se permiten imágenes JPG, PNG o WebP');
    }

    $ext       = match($mimeType) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        default      => 'jpg',
    };
    $filename  = 'car_' . uniqid() . '.' . $ext;
    $destPath  = UPLOAD_DIR . $filename;

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        fail('Error al guardar la imagen');
    }
    $imagenUrl = UPLOAD_URL . $filename;
}

$db   = Database::get();
$stmt = $db->prepare(
    'INSERT INTO autos (nombre, marca, modelo, `año`, tipo, precio_dia, descripcion,
                        caracteristicas, imagen_url, disponible, pasajeros, transmision, motor, creado_por)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([
    $nombre, $marca, $modelo, $anio, $tipo, $precio_dia, $descripcion,
    $caracteristicas, $imagenUrl, $disponible, $pasajeros, $transmision, $motor,
    $payload['user_id']
]);

$carId = (int) $db->lastInsertId();
log_action($payload['user_id'], 'CAR_CREATED', ['car_id' => $carId, 'nombre' => $nombre]);

ok(['id' => $carId, 'imagen_url' => $imagenUrl], 'Vehículo creado correctamente', 201);
