<?php
declare(strict_types=1);
require_once __DIR__.'/../config/conexao.php';
require_once __DIR__.'/../includes/funcoes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validarCsrf(post('csrf'))) {
    flash('erro', 'Formulário inválido.');
    redirect('login.php');
}

$pdo = getPDO();
if (!$pdo) {
    flash('erro', 'Banco de dados indisponível. Importe o banco/banco.sql.');
    redirect('login.php');
}

$email = strtolower(trim((string)post('email')));
$senha = (string)post('senha');

try {
    $st = $pdo->prepare('SELECT id,nome,email,senha FROM usuarios WHERE email=? AND ativo=1 LIMIT 1');
    $st->execute([$email]);
    $user = $st->fetch();

    if (!$user || !password_verify($senha, $user['senha'])) {
        flash('erro', 'E-mail ou senha inválidos.');
        redirect('login.php');
    }

    if (password_needs_rehash($user['senha'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($senha, PASSWORD_DEFAULT);
        $rehash = $pdo->prepare('UPDATE usuarios SET senha=? WHERE id=?');
        $rehash->execute([$newHash, (int)$user['id']]);
    }

    session_regenerate_id(true);
    renovarCsrfToken();
    $_SESSION['cliente_id'] = (int)$user['id'];
    $_SESSION['cliente_nome'] = $user['nome'];
    flash('sucesso', 'Login realizado com sucesso.');
    redirect('cliente/index.php');
} catch (Throwable $e) {
    flash('erro', 'Não foi possível entrar.');
    redirect('login.php');
}
