<?php
declare(strict_types=1);
require_once __DIR__.'/../config/seguranca.php';
exigirLogin();
$pageTitle='Meus pedidos';
$extraCss=['css/cliente.css'];$bodyClass='has-client-tabbar';
require __DIR__.'/../includes/header.php';
$pdo=getPDO(); $orders=[]; $errorMessage='';
if(!$pdo){ $errorMessage='Não foi possível conectar ao banco de dados.'; }
else{ try{ $st=$pdo->prepare('SELECT id,total,status,criado_em FROM pedidos WHERE usuario_id=? ORDER BY id DESC'); $st->execute([(int)$_SESSION['cliente_id']]); $orders=$st->fetchAll(); }catch(Throwable $e){ error_log('[Açai Flow] Histórico do cliente: '.$e->getMessage()); $errorMessage='Não foi possível carregar seus pedidos agora.'; } }
?>
<section class="section page-shell client-page client-area"><div class="container client-shell">
<?php $clientActive='pedidos';require __DIR__.'/../includes/cliente-nav.php'; ?>
<div class="client-content">
<div class="client-hero"><div class="client-hero-top"><div><span class="eyebrow">Histórico da conta</span><h1>Meus pedidos</h1><p>Consulte pedidos anteriores, valores, datas e acompanhe os detalhes de cada experiência no Flow.</p></div><div class="client-hero-actions"><a class="btn btn-coral" href="<?=url('monte-seu-acai.php')?>">NOVO PEDIDO →</a></div></div></div>
<section class="client-panel"><div class="client-panel-head"><div><h2>Histórico completo</h2><p><?=count($orders)?> pedido(s) encontrado(s) para esta conta.</p></div><a class="client-panel-link" href="<?=url('cliente/index.php')?>">MINHA ÁREA →</a></div>
<?php if($errorMessage): ?><div class="client-empty"><div class="client-empty-icon">!</div><h3>Não foi possível carregar o histórico</h3><p><?=e($errorMessage)?></p></div>
<?php elseif(!$orders): ?><div class="client-empty"><div class="client-empty-icon">▣</div><h3>Você ainda não possui pedidos</h3><p>Seu primeiro pedido aparecerá automaticamente aqui depois da confirmação.</p><a class="btn btn-bordo" href="<?=url('monte-seu-acai.php')?>">MONTAR MEU AÇAÍ →</a></div>
<?php else: ?><div class="client-table-wrap"><table class="client-table client-table-cards"><thead><tr><th>Pedido</th><th>Data</th><th>Valor</th><th>Status</th><th>Ação</th></tr></thead><tbody><?php foreach($orders as $order): ?><tr><td data-label="Pedido"><strong>#<?=e($order['id'])?></strong></td><td data-label="Data"><?=formatarData($order['criado_em'])?></td><td data-label="Valor"><?=formatarMoeda((float)$order['total'])?></td><td data-label="Status"><span class="status-pill <?=statusClasse($order['status'])?>"><?=e(statusLabel($order['status']))?></span></td><td data-label="Ação"><a class="mini-btn" href="<?=url('cliente/pedido-detalhes.php?id='.$order['id'])?>">Ver detalhes</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
</div></div></section>
<?php require __DIR__.'/../includes/footer.php'; ?>
