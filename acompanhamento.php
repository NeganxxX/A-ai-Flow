<?php
$pageTitle='Acompanhar pedido';
require_once __DIR__.'/config/seguranca.php';
exigirLogin();
require __DIR__.'/includes/header.php';
$pdo=getPDO();$orders=[];
if($pdo && tableExists($pdo,'pedidos')){ $st=$pdo->prepare('SELECT id,total,status,criado_em FROM pedidos WHERE usuario_id=? ORDER BY id DESC LIMIT 10');$st->execute([$_SESSION['cliente_id']]);$orders=$st->fetchAll(); }
?>
<section class="page-hero"><div class="container"><span class="eyebrow">Área do cliente</span><h1>Acompanhe seu <span>flow.</span></h1><p>Veja os pedidos recentes e o andamento de cada entrega.</p></div></section>
<section class="section page-shell"><div class="container">
<?php if(!$orders): ?><div class="empty-state">Você ainda não possui pedidos registrados.<br><br><a href="<?=url('monte-seu-acai.php')?>" class="btn btn-bordo btn-small">MONTAR MEU AÇAÍ</a></div><?php else: foreach($orders as $order): $steps=['recebido','confirmado','preparando','pronto','saiu_entrega','entregue'];$current=array_search($order['status'],$steps,true);if($current===false)$current=0; ?><article class="tracking-card" style="margin-bottom:18px"><div class="tracking-head"><div><span class="tag">Pedido #<?=e($order['id'])?></span><h2 style="margin-top:8px"><?=formatarMoeda((float)$order['total'])?></h2><p class="text-muted" style="font-size:.69rem"><?=formatarData($order['criado_em'])?></p></div><span class="status-pill <?=statusClasse($order['status'])?>"><?=e(statusLabel($order['status']))?></span></div><div class="progress-track"><?php foreach($steps as $idx=>$step):?><div class="progress-step <?=$idx<=$current?'done':''?>"><div class="progress-dot"></div><div class="progress-label"><?=e(statusLabel($step))?></div></div><?php endforeach;?></div><a class="btn btn-bordo btn-small" href="<?=url('cliente/pedido-detalhes.php?id='.$order['id'])?>">VER DETALHES</a></article><?php endforeach;endif; ?>
</div></section>
<?php require __DIR__.'/includes/footer.php'; ?>
