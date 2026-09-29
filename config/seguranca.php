<?php
declare(strict_types=1);
require_once __DIR__ . '/sessao.php';
require_once __DIR__ . '/../includes/funcoes.php';

function exigirLogin(): void {
    if ((int)($_SESSION['cliente_id'] ?? 0) <= 0) {
        flash('erro', 'Entre na sua conta para continuar.');
        redirect('login.php');
    }
}

function exigirAdmin(): void {
    if ((int)($_SESSION['admin_id'] ?? 0) <= 0) redirect('admin/login.php');
}
