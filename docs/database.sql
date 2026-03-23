-- =============================================================
-- RENTCAR v3 — Esquema de Base de Datos MySQL 8.x
-- Compatible con Laragon (MySQL 8 / MariaDB 10.6)
-- =============================================================
-- Pasos de instalación:
--   1. Abrir HeidiSQL en Laragon
--   2. Conectar con: 127.0.0.1 / root / sin contraseña
--   3. Importar este archivo (Archivo > Cargar archivo SQL)
--   4. Ejecutar http://rentcar.test/setup.php?key=rentcar-setup-2025
--      para crear usuarios con contraseñas reales
-- =============================================================

CREATE DATABASE IF NOT EXISTS rentcar
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE rentcar;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS logs;
DROP TABLE IF EXISTS carrito;
DROP TABLE IF EXISTS mensaje_contacto;
DROP TABLE IF EXISTS codigos_mfa;
DROP TABLE IF EXISTS sesiones;
DROP TABLE IF EXISTS autos;
DROP TABLE IF EXISTS usuarios;
SET FOREIGN_KEY_CHECKS = 1;

-- ─── TABLA: usuarios ──────────────────────────────────────────
CREATE TABLE usuarios (
    id               INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(120)    NOT NULL,
    apellido         VARCHAR(120)    NOT NULL,
    email            VARCHAR(200)    NOT NULL UNIQUE,
    password_hash    VARCHAR(255)    NOT NULL,
    rol              ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
    telefono         VARCHAR(20)     NULL,
    avatar_url       VARCHAR(500)    NULL,
    activo           TINYINT(1)      NOT NULL DEFAULT 1,
    email_verificado TINYINT(1)      NOT NULL DEFAULT 0,
    creado_en        DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email  (email),
    INDEX idx_rol    (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── TABLA: sesiones ──────────────────────────────────────────
CREATE TABLE sesiones (
    id          INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT UNSIGNED  NOT NULL,
    token_hash  VARCHAR(64)   NOT NULL UNIQUE,
    ip          VARCHAR(45)   NULL,
    user_agent  VARCHAR(512)  NULL,
    expira_en   DATETIME      NOT NULL,
    creado_en   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    cerrado_en  DATETIME      NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_token  (token_hash),
    INDEX idx_expira (expira_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── TABLA: codigos_mfa ───────────────────────────────────────
-- Almacena códigos MFA de 6 dígitos (login) y tokens de reset de contraseña
CREATE TABLE codigos_mfa (
    id          INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT UNSIGNED  NOT NULL,
    codigo      VARCHAR(64)   NOT NULL,
    tipo        ENUM('login','reset') NOT NULL DEFAULT 'login',
    usado       TINYINT(1)    NOT NULL DEFAULT 0,
    intentos    TINYINT       NOT NULL DEFAULT 0,
    expira_en   DATETIME      NOT NULL,
    creado_en   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_tipo (usuario_id, tipo),
    INDEX idx_expira       (expira_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── TABLA: autos ─────────────────────────────────────────────
CREATE TABLE autos (
    id              INT UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(150)    NOT NULL,
    marca           VARCHAR(80)     NOT NULL,
    modelo          VARCHAR(80)     NOT NULL,
    `año`           YEAR            NOT NULL,
    tipo            ENUM('SUV','Sedan','Deportivo','Pickup','Eléctrico','Minivan') NOT NULL,
    precio_dia      DECIMAL(10,2)   NOT NULL,
    descripcion     TEXT            NULL,
    caracteristicas JSON            NULL,
    imagen_url      VARCHAR(500)    NULL,
    disponible      TINYINT(1)      NOT NULL DEFAULT 1,
    pasajeros       TINYINT         NOT NULL DEFAULT 5,
    transmision     ENUM('Automático','Manual','CVT') NOT NULL DEFAULT 'Automático',
    motor           VARCHAR(80)     NULL,
    creado_por      INT UNSIGNED    NULL,
    creado_en       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_tipo       (tipo),
    INDEX idx_disponible (disponible),
    FULLTEXT idx_ft      (nombre, descripcion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── TABLA: carrito ───────────────────────────────────────────
CREATE TABLE carrito (
    id             INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    usuario_id     INT UNSIGNED  NOT NULL,
    auto_id        INT UNSIGNED  NOT NULL,
    dias_renta     INT           NOT NULL DEFAULT 1,
    fecha_inicio   DATE          NULL,
    fecha_fin      DATE          NULL,
    creado_en      DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_usuario_auto (usuario_id, auto_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (auto_id)    REFERENCES autos(id)    ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── TABLA: mensaje_contacto ──────────────────────────────────
CREATE TABLE mensaje_contacto (
    id          INT UNSIGNED  AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT UNSIGNED  NULL,
    nombre      VARCHAR(150)  NOT NULL,
    email       VARCHAR(200)  NOT NULL,
    asunto      VARCHAR(255)  NULL,
    mensaje     TEXT          NOT NULL,
    ip          VARCHAR(45)   NULL,
    leido       TINYINT(1)    NOT NULL DEFAULT 0,
    creado_en   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_leido  (leido),
    INDEX idx_creado (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── TABLA: logs ──────────────────────────────────────────────
CREATE TABLE logs (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT UNSIGNED    NULL,
    accion      VARCHAR(80)     NOT NULL,
    detalle     JSON            NULL,
    ip          VARCHAR(45)     NULL,
    user_agent  VARCHAR(512)    NULL,
    creado_en   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_accion  (accion),
    INDEX idx_creado  (creado_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ─── VISTAS ───────────────────────────────────────────────────
CREATE OR REPLACE VIEW v_carrito_detalle AS
SELECT c.usuario_id, u.nombre AS usuario_nombre,
       a.id AS auto_id, a.nombre AS auto_nombre, a.tipo,
       a.precio_dia, c.dias_renta,
       (a.precio_dia * c.dias_renta) AS subtotal,
       c.fecha_inicio, c.fecha_fin
FROM carrito c
JOIN autos    a ON a.id = c.auto_id
JOIN usuarios u ON u.id = c.usuario_id;

CREATE OR REPLACE VIEW v_mensajes_nuevos AS
SELECT id, nombre, email, asunto, LEFT(mensaje,100) AS preview, creado_en
FROM mensaje_contacto WHERE leido = 0 ORDER BY creado_en DESC;

-- =============================================================
-- ESQUEMA IMPORTADO CORRECTAMENTE.
-- SIGUIENTE PASO OBLIGATORIO:
--   Abre en tu navegador:
--   http://rentcar.test/setup.php?key=rentcar-setup-2025
--   Esto creará los usuarios admin y cliente con contraseñas reales.
-- =============================================================
