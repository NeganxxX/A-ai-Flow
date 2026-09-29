<?php
declare(strict_types=1);
require_once __DIR__ . '/app.php';
function getPDO(): ?PDO {
    static $pdo = null, $tried = false;
    if ($pdo instanceof PDO) return $pdo;
    if ($tried) return null;
    $tried = true;
    try {
        $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4', DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
        return $pdo;
    } catch (Throwable $e) {
        error_log('[Açai Flow] Erro de conexão MySQL: ' . $e->getMessage());
        return null;
    }
}
