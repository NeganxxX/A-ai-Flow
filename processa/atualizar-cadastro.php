<?php
declare(strict_types=1);
require_once __DIR__.'/../config/seguranca.php';
exigirLogin();
require_once __DIR__.'/../config/conexao.php';
require_once __DIR__.'/../includes/funcoes.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validarCsrf(post('csrf'))) {
    flash('erro', 'Formulário inválido.');
    redirect('cliente/meus-dados.php');
}

$pdo = getPDO();
if (!$pdo) {
    flash('erro', 'Banco de dados indisponível.');
    redirect('cliente/meus-dados.php');
}

$id = (int)$_SESSION['cliente_id'];
$nome = trim((string)post('nome'));
$telefone = trim((string)post('telefone'));
$senha = (string)post('senha');

if (mb_strlen($nome) < 3 || mb_strlen($nome) > 120) {
    flash('erro', 'Informe um nome válido.');
    redirect('cliente/meus-dados.php');
}

try {

    if ($senha !== '') {
        if (strlen($senha) < 8) throw new RuntimeException('A nova senha deve ter pelo menos 8 caracteres.');
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $st = $pdo->prepare('UPDATE usuarios SET nome=?,telefone=?,senha=? WHERE id=?');
        $st->execute([$nome, $telefone !== '' ? $telefone : null, $hash, $id]);
    } else {
        $st = $pdo->prepare('UPDATE usuarios SET nome=?,telefone=? WHERE id=?');
        $st->execute([$nome, $telefone !== '' ? $telefone : null, $id]);
    }

    $_SESSION['cliente_nome'] = $nome;
    flash('sucesso', 'Dados atualizados.');
    redirect('cliente/meus-dados.php');
} catch (Throwable $e) {
    flash('erro', $e->getMessage());
    redirect('cliente/meus-dados.php');
}
