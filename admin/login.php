<?php
declare(strict_types=1);
require_once __DIR__.'/../config/conexao.php';
require_once __DIR__.'/../includes/funcoes.php';

if (isAdminLogado()) redirect('admin/dashboard.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validarCsrf(post('csrf'))) {
        $error = 'Formulário inválido.';
    } else {
        $pdo = getPDO();
        if (!$pdo) {
            $error = 'Banco indisponível. Importe banco/banco.sql.';
        } else {
            try {
                $email = strtolower(trim((string)post('email')));
                $senha = (string)post('senha');
                $st = $pdo->prepare('SELECT id,nome,email,senha FROM administradores WHERE email=? AND ativo=1 LIMIT 1');
                $st->execute([$email]);
                $admin = $st->fetch();

                if (!$admin || !password_verify($senha, $admin['senha'])) {
                    $error = 'E-mail ou senha inválidos.';
                } else {
                    if (password_needs_rehash($admin['senha'], PASSWORD_DEFAULT)) {
                        $newHash = password_hash($senha, PASSWORD_DEFAULT);
                        $rehash = $pdo->prepare('UPDATE administradores SET senha=? WHERE id=?');
                        $rehash->execute([$newHash, (int)$admin['id']]);
                    }

                    session_regenerate_id(true);
                    renovarCsrfToken();
                    $_SESSION['admin_id'] = (int)$admin['id'];
                    $_SESSION['admin_nome'] = $admin['nome'];

                    $pdo->prepare('UPDATE administradores SET ultimo_login_em=NOW() WHERE id=?')->execute([(int)$admin['id']]);
                    registrarLogAdmin($pdo, (int)$admin['id'], 'login', 'administradores', (int)$admin['id'], 'Login realizado no painel');

                    redirect('admin/dashboard.php');
                }
            } catch (Throwable $e) {
                $error = 'Não foi possível entrar no painel.';
            }
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin | Açaí Flow</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script>try{if(localStorage.getItem('acaiFlowTheme')==='dark'){document.documentElement.classList.add('dark-mode');}}catch(e){}</script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Pacifico&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?=asset('css/style.css')?>?v=<?=filemtime(__DIR__.'/../css/style.css')?>">
    <link rel="stylesheet" href="<?=asset('css/tema.css')?>?v=<?=filemtime(__DIR__.'/../css/tema.css')?>">
    <link rel="stylesheet" href="<?=asset('css/admin.css')?>?v=<?=filemtime(__DIR__.'/../css/admin.css')?>">
</head>
<body class="admin-body">
<div class="admin-login-wrap">
    <div class="admin-login-card">
        <button class="theme-toggle theme-toggle-admin admin-login-theme" type="button" aria-label="Ativar modo noturno" aria-pressed="false" title="Ativar modo noturno">
            <svg class="theme-icon theme-moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 14.2A8.8 8.8 0 0 1 9.8 3.2a8.9 8.9 0 1 0 11 11Z"/></svg>
            <svg class="theme-icon theme-sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
        </button>
        <a class="brand" href="<?=url('index.php')?>">
            <img src="<?=asset('img/logo/logo-principal.svg')?>" alt="Açaí Flow">
        </a>
        <h1>Acesso administrativo</h1>
        <p>Área restrita para gestão de produtos, estoque, pedidos e clientes.</p>
        <?php if($error): ?>
            <div class="alert alert-danger"><?=e($error)?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <input type="hidden" name="csrf" value="<?=csrfToken()?>">
            <div class="form-group" style="margin-bottom:13px">
                <label for="admin-email">E-mail</label>
                <input id="admin-email" class="form-control" type="email" name="email" autocomplete="username" required maxlength="180" value="<?=e(post('email'))?>">
            </div>
            <div class="form-group">
                <label for="admin-senha">Senha</label>
                <input id="admin-senha" class="form-control" type="password" name="senha" autocomplete="current-password" required minlength="8">
            </div>
            <button class="btn btn-bordo" style="width:100%;margin-top:16px">ENTRAR NO PAINEL →</button>
        </form>
        <div class="auth-links"><a href="<?=url('index.php')?>">Voltar ao site</a></div>
    </div>
</div>
<script src="<?=asset('js/tema.js')?>" defer></script>
</body>
</html>
