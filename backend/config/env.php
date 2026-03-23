<?php
// =============================================================
// backend/config/env.php
// Configuración de entorno — Laragon
// =============================================================

// ── Base de datos (Laragon: 127.0.0.1, root sin contraseña) ──
define('DB_HOST', '127.0.0.1');   // IMPORTANTE: usar 127.0.0.1 no "localhost" en Laragon
define('DB_PORT', 3306);
define('DB_NAME', 'rentcar');
define('DB_USER', 'root');
define('DB_PASS', '');            // Laragon usa root sin contraseña por defecto
define('DB_CHARSET', 'utf8mb4');

// ── JWT ────────────────────────────────────────────────────
define('JWT_SECRET', '6OLmvMov9OsUPtOFh1fSZ3ziP1YNdARjlQN1JvMolT0P6vfW8tEfnFFJ0sdew7CB');
define('JWT_EXPIRY_HOURS', 24);        // duración del token de sesión
define('JWT_TEMP_EXPIRY_MIN', 10);     // token temporal pre-MFA

// ── SMTP (para envío real de correos) ─────────────────────
// Usa Gmail App Password o cualquier proveedor SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'leyvajesus297@gmail.com');
define('SMTP_PASS', 'haui pqex bycm issd');    // Gmail: Configuración > App passwords
define('SMTP_FROM', 'no-reply@rentcar.mx');
define('SMTP_NAME', 'RentCar');

// ── App ────────────────────────────────────────────────────
define('APP_NAME', 'RentCar');
// Laragon crea dominios .test automáticamente según el nombre de carpeta.
// Si tu carpeta se llama "rentcar", el dominio es http://rentcar.test
// Ajusta APP_URL al dominio que Laragon te asignó:
define('APP_URL', 'http://rentcar.test');     // ← cambia si usas otro dominio
define('API_URL', 'http://rentcar.test/api'); // ← misma base + /api

// ── Seguridad ──────────────────────────────────────────────
define('MFA_CODE_EXPIRY_MIN', 10);       // minutos antes de expirar código MFA
define('MFA_MAX_ATTEMPTS', 5);           // intentos máximos antes de invalidar
define('RESET_EXPIRY_MIN', 30);          // minutos para link de reset
define('BCRYPT_COST', 12);               // factor de costo bcrypt

// ── Uploads ────────────────────────────────────────────────
define('UPLOAD_DIR', __DIR__ . '/../public/uploads/');
define('UPLOAD_URL', API_URL . '/uploads/');
define('MAX_FILE_SIZE_MB', 5);
define('ALLOWED_IMG_TYPES', ['image/jpeg','image/png','image/webp']);
