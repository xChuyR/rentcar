<?php
/**
 * RENTCAR v3 — Script de instalación inicial
 * ============================================
 * Ejecutar UNA SOLA VEZ después de importar el esquema database.sql:
 *
 *   Opción A (navegador): http://rentcar.test/setup.php
 *   Opción B (terminal):  php backend/public/setup.php
 *
 * ELIMINA ESTE ARCHIVO después de ejecutarlo.
 */

// Clave simple para evitar que cualquiera lo ejecute
define('SETUP_KEY', 'rentcar-setup-2025');

if (php_sapi_name() !== 'cli') {
    $key = $_GET['key'] ?? '';
    if ($key !== SETUP_KEY) {
        http_response_code(403);
        die('<h1>403 Forbidden</h1><p>Accede con ?key=rentcar-setup-2025</p>');
    }
}

require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/html; charset=utf-8');

echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
<title>RentCar Setup</title>
<style>
  body{font-family:monospace;background:#0f0e0c;color:#f0ece4;padding:2rem;line-height:1.8}
  .ok{color:#1a7a4a} .err{color:#c0392b} .info{color:#c8982a}
  h1{color:#c8982a;font-family:serif} pre{background:#1c1a17;padding:1rem;border-radius:.5rem;overflow:auto}
</style></head><body>';
echo '<h1>🚗 RentCar v3 — Setup inicial</h1>';

$db = Database::get();
$log = [];

try {

    // ── 1. Verificar tablas ──────────────────────────────────
    $tables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $required = ['usuarios','sesiones','codigos_mfa','autos','carrito','mensaje_contacto','logs'];
    $missing  = array_diff($required, $tables);

    if ($missing) {
        echo '<p class="err">❌ Faltan tablas: ' . implode(', ', $missing) . '</p>';
        echo '<p class="info">💡 Importa primero: <code>mysql -u root rentcar &lt; docs/database.sql</code></p>';
        die('</body></html>');
    }
    echo '<p class="ok">✅ Todas las tablas existen (' . count($required) . ')</p>';

    // ── 2. Limpiar usuarios semilla anteriores ───────────────
    $db->exec("DELETE FROM usuarios WHERE email IN ('admin@rentcar.mx','cliente@rentcar.mx')");

    // ── 3. Insertar admin con hash correcto ──────────────────
    $adminHash = password_hash('Admin2025!', PASSWORD_BCRYPT, ['cost' => 12]);
    $db->prepare(
        "INSERT INTO usuarios (nombre, apellido, email, password_hash, rol, activo, email_verificado)
         VALUES ('Admin','RentCar','admin@rentcar.mx',?,'admin',1,1)"
    )->execute([$adminHash]);
    echo '<p class="ok">✅ Admin creado: admin@rentcar.mx / Admin2025!</p>';

    // ── 4. Insertar cliente demo ─────────────────────────────
    $clienteHash = password_hash('Cliente2025!', PASSWORD_BCRYPT, ['cost' => 12]);
    $db->prepare(
        "INSERT INTO usuarios (nombre, apellido, email, password_hash, rol, activo, email_verificado)
         VALUES ('Ana','García','cliente@rentcar.mx',?,'cliente',1,1)"
    )->execute([$clienteHash]);
    echo '<p class="ok">✅ Cliente demo: cliente@rentcar.mx / Cliente2025!</p>';

    // ── 5. Verificar/insertar autos si no existen ────────────
    $autoCount = (int)$db->query("SELECT COUNT(*) FROM autos")->fetchColumn();
    if ($autoCount === 0) {
        $adminId = (int)$db->query("SELECT id FROM usuarios WHERE email='admin@rentcar.mx'")->fetchColumn();

        $autos = [
            ['Toyota RAV4 Hybrid','Toyota','RAV4 Hybrid',2024,'SUV',950.00,
             'SUV híbrida con tracción total y tecnología avanzada.',
             '["GPS","Bluetooth","Apple CarPlay","A/C","Tracción 4x4","Lane Assist"]',5,'Automático','2.5L Híbrido 219hp'],
            ['Honda Accord','Honda','Accord',2024,'Sedan',750.00,
             'Sedán elegante con Honda Sensing y excelente eficiencia de combustible.',
             '["GPS","Apple CarPlay","Calefacción asientos","A/C","Honda Sensing"]',5,'Automático','1.5L Turbo 192hp'],
            ['Ford Mustang GT','Ford','Mustang GT Fastback',2023,'Deportivo',1800.00,
             'El ícono americano. V8 450hp para quienes buscan adrenalina.',
             '["B&O Audio","Sport Mode","Track Mode","Asientos cuero","Launch Control"]',2,'Manual','5.0L V8 450hp'],
            ['Ford F-150 Raptor','Ford','F-150 Raptor',2024,'Pickup',1400.00,
             'La pickup más capaz. Off-road extremo y carga sin límites.',
             '["GPS","4x4","Remolque 3.7T","A/C bizona","Cámara 360°","Android Auto"]',5,'Automático','3.5L EcoBoost V6 450hp'],
            ['Tesla Model 3','Tesla','Model 3 Long Range',2024,'Eléctrico',1100.00,
             '100% eléctrico con 580km de autonomía. Autopilot incluido.',
             '["Autopilot","580km autonomía","Carga rápida","Pantalla 15\"","OTA Updates"]',5,'Automático','Dual Motor Eléctrico 346hp'],
            ['Mazda 6 Signature','Mazda','6 Signature',2024,'Sedan',880.00,
             'El sedán más refinado. Audio BOSE y cuero ventilado.',
             '["BOSE 12 altavoces","Cuero ventilado","Head-Up Display","A/C trizona"]',5,'Automático','2.5L Turbo 256hp'],
        ];

        $stmt = $db->prepare(
            "INSERT INTO autos (nombre,marca,modelo,año,tipo,precio_dia,descripcion,caracteristicas,pasajeros,transmision,motor,creado_por)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        foreach ($autos as $a) {
            $stmt->execute(array_merge($a, [$adminId]));
        }
        echo '<p class="ok">✅ ' . count($autos) . ' autos de ejemplo insertados</p>';
    } else {
        echo '<p class="info">ℹ️ Ya existen ' . $autoCount . ' autos en la BD (no se sobrescribieron)</p>';
    }

    // ── 6. Test de conexión y resumen ────────────────────────
    $counts = [];
    foreach (['usuarios','autos','carrito','logs'] as $t) {
        $counts[$t] = $db->query("SELECT COUNT(*) FROM $t")->fetchColumn();
    }

    echo '<br><h2 class="info">📊 Estado de la base de datos</h2><pre>';
    foreach ($counts as $t => $c) {
        echo str_pad($t, 20) . ': ' . $c . " registros\n";
    }
    echo '</pre>';

    echo '<br><h2 class="ok">✅ Setup completado con éxito</h2>';
    echo '<p>Credenciales configuradas:</p>';
    echo '<pre>';
    echo "Admin   : admin@rentcar.mx   / Admin2025!\n";
    echo "Cliente : cliente@rentcar.mx / Cliente2025!\n";
    echo '</pre>';

    echo '<p class="err">⚠️ <strong>IMPORTANTE: Elimina este archivo ahora</strong><br>';
    echo 'En terminal: <code>rm backend/public/setup.php</code></p>';

} catch (Throwable $e) {
    echo '<p class="err">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<pre class="err">' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}

echo '</body></html>';
