<?php
// backend/public/index.php
// Punto de entrada único de la API REST.
// En Laragon: carpeta www/rentcar/backend/public → accesible en http://rentcar.test/api/
// El Virtual Host de Laragon tiene: Alias /api → .../backend/public

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../utils/response.php';
require_once __DIR__ . '/../utils/jwt.php';
require_once __DIR__ . '/../utils/mailer.php';
require_once __DIR__ . '/../middleware/auth.php';

// Cabeceras CORS siempre primero
cors();

// ── Parsear ruta ──────────────────────────────────────────
// Quitar el prefijo /api si existe (cuando viene del Virtual Host)
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalizar: quitar /api, /api/, también /rentcar/api si hay subfolder
$uri = preg_replace('#^(/[^/]+)?/api/?#', '', $uri);
$uri = trim($uri, '/');

$parts    = $uri ? array_values(array_filter(explode('/', $uri))) : [];
$method   = $_SERVER['REQUEST_METHOD'];

// Method override shim for multipart PUT/DELETE
if ($method === 'POST' && isset($_POST['_method'])) {
    $method = strtoupper($_POST['_method']);
}

$resource = $parts[0] ?? '';
$sub      = $parts[1] ?? '';
$id       = isset($parts[2]) ? (int)$parts[2] : null;
// Para /cars/5 → resource=cars, id=5 (sub=5 también, verificar)
// /admin/users/3 → resource=admin, sub=users, id=3
if ($resource === 'cars' && is_numeric($sub)) {
    $id  = (int)$sub;
    $sub = '';
}
if ($resource === 'admin' && isset($parts[2]) && is_numeric($parts[2])) {
    $id = (int)$parts[2];
}

// ── Tabla de rutas ─────────────────────────────────────────
match(true) {
    // Auth
    $resource === 'auth' && $sub === 'register'       => require __DIR__ . '/../api/auth/register.php',
    $resource === 'auth' && $sub === 'login'          => require __DIR__ . '/../api/auth/login.php',
    $resource === 'auth' && $sub === 'verify-mfa'     => require __DIR__ . '/../api/auth/verify-mfa.php',
    $resource === 'auth' && $sub === 'request-reset'  => require __DIR__ . '/../api/auth/request-reset.php',
    $resource === 'auth' && $sub === 'reset-password' => require __DIR__ . '/../api/auth/reset-password.php',
    $resource === 'auth' && $sub === 'logout'         => require __DIR__ . '/../api/auth/logout.php',
    $resource === 'auth' && $sub === 'me'             => require __DIR__ . '/../api/auth/me.php',

    // Cars
    $resource === 'cars' && $method === 'GET' && !$id  => require __DIR__ . '/../api/cars/index.php',
    $resource === 'cars' && $method === 'GET' && $id   => require __DIR__ . '/../api/cars/show.php',
    $resource === 'cars' && $method === 'POST'         => require __DIR__ . '/../api/cars/store.php',
    $resource === 'cars' && $method === 'PUT'          => require __DIR__ . '/../api/cars/update.php',
    $resource === 'cars' && $method === 'DELETE'       => require __DIR__ . '/../api/cars/delete.php',

    // Cart
    $resource === 'cart' => require __DIR__ . '/../api/cart/index.php',

    // Contact
    $resource === 'contact' => require __DIR__ . '/../api/contact/store.php',

    // Admin
    $resource === 'admin' && $sub === 'dashboard' => require __DIR__ . '/../api/admin/dashboard.php',
    $resource === 'admin' && $sub === 'users'     => require __DIR__ . '/../api/admin/users.php',

    // Health check
    $resource === 'ping' => (function() { ok(['pong' => true, 'time' => date('c')]); })(),

    default => fail("Ruta no encontrada: /{$resource}/{$sub}", 404),
};
