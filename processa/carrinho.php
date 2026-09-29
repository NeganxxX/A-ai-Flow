<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/seguranca.php';
require_once __DIR__ . '/../includes/funcoes.php';

header('Content-Type: application/json; charset=utf-8');

function cartResponse(bool $ok, array $data = [], int $status = 200): never {
    http_response_code($status);
    echo json_encode(array_merge(['ok' => $ok], $data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function normalizarCarrinho(array $cart): array {
    $normalized = [];

    foreach (array_slice($cart, 0, 50) as $item) {
        if (!is_array($item)) continue;

        $normalized[] = [
            'signature' => isset($item['signature']) ? substr((string)$item['signature'], 0, 160) : null,
            'product_id' => isset($item['product_id']) && $item['product_id'] !== null ? (int)$item['product_id'] : null,
            'size_id' => isset($item['size_id']) ? (int)$item['size_id'] : null,
            'size_type' => isset($item['size_type']) ? substr((string)$item['size_type'], 0, 20) : null,
            'name' => isset($item['name']) ? substr(strip_tags((string)$item['name']), 0, 180) : 'Açaí Flow',
            'price' => isset($item['price']) ? max(0, round((float)$item['price'], 2)) : 0,
            'quantity' => max(1, min(20, (int)($item['quantity'] ?? 1))),
            'image' => isset($item['image']) ? substr((string)$item['image'], 0, 255) : '',
            'personalization' => isset($item['personalization']) && is_array($item['personalization'])
                ? $item['personalization']
                : null,
        ];
    }

    return $normalized;
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

if ($method === 'GET') {
    cartResponse(true, ['cart' => $_SESSION['cart'] ?? []]);
}

if ($method !== 'POST') {
    cartResponse(false, ['message' => 'Método não permitido.'], 405);
}

if (!validarCsrf((string)($_POST['csrf'] ?? ''))) {
    cartResponse(false, ['message' => 'Sessão de segurança inválida. Atualize a página.'], 419);
}

$cartJson = (string)($_POST['cart_json'] ?? '[]');
$decoded = json_decode($cartJson, true);
if (!is_array($decoded)) {
    cartResponse(false, ['message' => 'Formato de carrinho inválido.'], 422);
}

$_SESSION['cart'] = normalizarCarrinho($decoded);

cartResponse(true, [
    'cart' => $_SESSION['cart'],
    'count' => array_sum(array_map(static fn(array $item): int => (int)$item['quantity'], $_SESSION['cart'])),
]);
