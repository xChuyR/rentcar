<?php
// backend/config/db.php
require_once __DIR__ . '/env.php';

class Database {
    private static ?PDO $instance = null;

    public static function get(): PDO {
        if (self::$instance === null) {
            // Incluir puerto explícitamente — necesario en Laragon con 127.0.0.1
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                DB_HOST,
                defined('DB_PORT') ? DB_PORT : 3306,
                DB_NAME,
                DB_CHARSET
            );
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_FOUND_ROWS   => true,
            ];
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Mostrar error detallado en desarrollo para poder diagnosticar
                http_response_code(503);
                header('Content-Type: application/json');
                echo json_encode([
                    'ok'    => false,
                    'error' => 'Database connection failed',
                    'debug' => $e->getMessage(), // quitar en producción
                ]);
                exit;
            }
        }
        return self::$instance;
    }
}
