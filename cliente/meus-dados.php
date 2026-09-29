<?php
declare(strict_types=1);
require_once __DIR__.'/../config/seguranca.php';
exigirLogin();
$pageTitle='Meus dados';
$extraCss=['css/cliente.css'];$bodyClass='has-client-tabbar';
require __DIR__.'/../includes/header.php';
$pdo=getPDO();$user=null;
if($pdo){try{$st=$pdo->prepare('SELECT nome,email,telefone FROM usuarios WHERE id=?');$st->execute([$_SESSION['cliente_id']]);$user=$st->fetch();}catch(Throwable $e){error_log('[Açai Flow] Meus dados: '.$e->getMessage());}}
$name=(string)($user['nome']??$_SESSION['cliente_nome']??'Cliente');
$initials=mb_strtoupper(mb_substr(trim($name),0,1));
?>
<section class="section page-shell client-page client-area"><div class="container client-shell">
<?php $clientActive='dados';$clientName=$name;require __DIR__.'/../includes/cliente-nav.php'; ?>
<div class="client-content">
<div class="client-hero"><div class="client-hero-top"><div><span class="eyebrow">Minha conta</span><h1>Meus dados</h1><p>Atualize suas informações de contato e mantenha sua conta pronta para o próximo pedido.</p></div><div class="client-hero-actions"><a class="btn btn-outline" href="<?=url('cliente/index.php')?>">MINHA ÁREA</a></div></div></div>
<?php if(!$user): ?><div class="client-empty"><div class="client-empty-icon">!</div><h3>Não foi possível carregar seus dados</h3><p>Tente novamente em alguns instantes.</p></div>
<?php else: ?>
<div class="client-data-card">
<div class="client-form-head"><div class="client-form-avatar"><?=e($initials)?></div><div><h1>Perfil do cliente</h1><p>Seus dados pessoais ficam associados à sua conta Açaí Flow.</p></div></div>
<form action="<?=url('processa/atualizar-cadastro.php')?>" method="post"><input type="hidden" name="csrf" value="<?=csrfToken()?>">
<div class="client-form-section"><h2>Informações pessoais</h2><div class="client-form-grid"><div class="form-group full"><label>Nome completo</label><input class="form-control" name="nome" required value="<?=e($user['nome'])?>"></div><div class="form-group"><label>E-mail</label><input class="form-control" value="<?=e($user['email'])?>" readonly></div><div class="form-group"><label>WhatsApp</label><input class="form-control" name="telefone" value="<?=e($user['telefone'])?>" placeholder="(00) 00000-0000"></div></div></div>
<div class="client-form-section"><h2>Segurança</h2><div class="client-form-grid"><div class="form-group full"><label>Nova senha</label><input class="form-control" type="password" name="senha" minlength="6" placeholder="Deixe em branco para manter a senha atual"><small class="form-help">Use uma senha que você não utilize em outros serviços.</small></div></div></div>
<div class="client-form-actions"><button class="btn btn-bordo" type="submit">SALVAR ALTERAÇÕES →</button><a class="btn btn-outline" href="<?=url('cliente/index.php')?>">CANCELAR</a></div>
</form>
</div>
<?php endif; ?>
</div></div></section>
<?php require __DIR__.'/../includes/footer.php'; ?>
