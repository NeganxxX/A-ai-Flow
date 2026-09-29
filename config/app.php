<?php
declare(strict_types=1);

if (!defined('BASE_URL')) {
    $projectRoot = str_replace('\\', '/', dirname(__DIR__));
    $documentRoot = str_replace('\\', '/', (string)($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $projectRoot = rtrim($projectRoot, '/');
    $documentRoot = rtrim($documentRoot, '/');

    $base = '';

    // Em Apache/XAMPP: descobre automaticamente a pasta do projeto.
    if ($documentRoot !== '' && $projectRoot !== '') {
        if ($projectRoot === $documentRoot) {
            $base = '';
        } elseif (str_starts_with($projectRoot, $documentRoot . '/')) {
            $relative = ltrim(substr($projectRoot, strlen($documentRoot)), '/');
            $base = $relative !== '' ? '/' . $relative : '';
        }
    }

    // Fallback seguro para o nome atual do projeto no XAMPP.
    if ($base === '') {
        $folderName = basename($projectRoot);
        $base = $folderName !== '' ? '/' . $folderName : '';
    }

    define('BASE_URL', rtrim($base, '/'));
}

define('SITE_NAME', 'Açaí Flow');
define('SITE_SLOGAN', 'Seu açaí. Seu flow.');

define('SITE_HORARIO_SEMANA', 'Seg–Sex · 10h às 22h');
define('SITE_HORARIO_SABADO', 'Sáb · 10h às 16h');

$localConfig = __DIR__ . '/local.php';
if (is_file($localConfig)) {
    require $localConfig;
}

if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'acai_flow');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
