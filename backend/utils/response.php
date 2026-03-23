<?php
// backend/utils/response.php

function cors(): void {
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
    header("Access-Control-Allow-Origin: {$origin}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(204);
        exit;
    }
}

function ok(mixed $data = null, string $message = 'OK', int $code = 200): never {
    http_response_code($code);
    echo json_encode(['ok' => true, 'message' => $message, 'data' => $data]);
    exit;
}

function fail(string $error, int $code = 400, mixed $data = null): never {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $error, 'data' => $data]);
    exit;
}

function body(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

function ip(): string {
    return $_SERVER['HTTP_X_FORWARDED_FOR']
        ?? $_SERVER['HTTP_X_REAL_IP']
        ?? $_SERVER['REMOTE_ADDR']
        ?? '0.0.0.0';
}

function log_action(int|null $userId, string $accion, array $detalle = []): void {
    try {
        $db = Database::get();
        $stmt = $db->prepare(
            'INSERT INTO logs (usuario_id, accion, detalle, ip, user_agent)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $userId,
            $accion,
            json_encode($detalle),
            ip(),
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512)
        ]);
    } catch (Throwable) {}
}
