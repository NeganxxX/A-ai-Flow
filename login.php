<?php
$pageTitle='Entrar';
$extraCss=['css/login.css'];
require __DIR__.'/includes/header.php';
if(isClienteLogado()) redirect('cliente/index.php');
?><section class="auth-layout"><div class="auth-art"><div class="auth-art-content"><span class="eyebrow">Açaí Flow</span><h1>Vem pro<br>flow!</h1><p>Entre para acompanhar seus pedidos e deixar seu próximo açaí do jeitinho que você gosta.</p></div></div><div class="auth-form-side"><div class="auth-form-card"><a class="brand" href="<?=url('index.php')?>"><img src="<?=asset('img/logo/logo-principal.svg')?>" alt="Açaí Flow"></a><h2>Bem-vindo de volta</h2><p>Acesse sua área do cliente.</p><form action="<?=url('processa/login.php')?>" method="post"><input type="hidden" name="csrf" value="<?=csrfToken()?>"><div class="form-group"><label>E-mail</label><input class="form-control" type="email" name="email" required autocomplete="email"></div><div class="form-group"><label>Senha</label><input class="form-control" id="loginSenha" type="password" name="senha" required autocomplete="current-password"></div><button class="btn btn-bordo" style="width:100%;margin-top:10px">ENTRAR →</button></form><div class="auth-links"><a href="<?=url('cadastro.php')?>">Criar minha conta</a></div></div></div></section>
<?php require __DIR__.'/includes/footer.php'; ?>
