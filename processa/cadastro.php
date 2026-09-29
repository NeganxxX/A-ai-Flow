<?php
declare(strict_types=1);
require_once __DIR__.'/../config/conexao.php';
require_once __DIR__.'/../includes/funcoes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validarCsrf(post('csrf'))) {
    flash('erro', 'Não foi possível validar o formulário.');
    redirect('cadastro.php');
}

$pdo = getPDO();
if (!$pdo) {
    flash('erro', 'Banco de dados indisponível. Importe banco/banco.sql no phpMyAdmin e confira config/app.php.');
    redirect('cadastro.php');
}

$nome = trim((string)post('nome'));
$email = strtolower(trim((string)post('email')));
$telefone = trim((string)post('telefone'));
$senha = (string)post('senha');

if (mb_strlen($nome) < 3 || mb_strlen($nome) > 120 || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($senha) < 8) {
    flash('erro', 'Preencha os dados corretamente. A senha deve ter pelo menos 8 caracteres.');
    redirect('cadastro.php');
}

try {
    $st = $pdo->prepare('SELECT id FROM usuarios WHERE email=? LIMIT 1');
    $st->execute([$email]);
    if ($st->fetch()) {
        flash('erro', 'Já existe uma conta com esse e-mail.');
        redirect('login.php');
    }

    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $st = $pdo->prepare('INSERT INTO usuarios(nome,email,telefone,senha,ativo) VALUES(?,?,?,?,1)');
    $st->execute([$nome, $email, $telefone !== '' ? $telefone : null, $hash]);

    session_regenerate_id(true);
    renovarCsrfToken();
    $_SESSION['cliente_id'] = (int)$pdo->lastInsertId();
    $_SESSION['cliente_nome'] = $nome;
    flash('sucesso', 'Conta criada. Bem-vindo(a) ao Flow!');
    redirect('cliente/index.php');
} catch (Throwable $e) {
    flash('erro', 'Não foi possível criar a conta. Verifique os dados informados.');
    redirect('cadastro.php');
}
