<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    // Mantém a sessão somente via cookie, com nome dedicado ao projeto.
    session_name('acai_flow_session');
    // Reduz risco de fixação de sessão e impede que JavaScript leia o cookie.
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');

    $secure = !empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}
