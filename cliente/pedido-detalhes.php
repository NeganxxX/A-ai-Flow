<?php
declare(strict_types=1);
require_once __DIR__.'/../config/seguranca.php';
exigirLogin();
$pageTitle='Detalhes do pedido';
$extraCss=['css/cliente.css'];$bodyClass='has-client-tabbar';
require __DIR__.'/../includes/header.php';
$pdo=getPDO();$id=(int)($_GET['id']??0);$order=null;$items=[];
if($pdo){
 try{
  $st=$pdo->prepare('SELECT * FROM pedidos WHERE id=? AND usuario_id=? LIMIT 1');$st->execute([$id,$_SESSION['cliente_id']]);$order=$st->fetch();
  if($order){$st=$pdo->prepare('SELECT * FROM itens_pedido WHERE pedido_id=? ORDER BY id');$st->execute([$id]);$items=$st->fetchAll();}
 }catch(Throwable $e){error_log('[Açai Flow] Detalhes pedido: '.$e->getMessage());}
}
$steps=['recebido','confirmado','preparando','pronto','saiu_entrega','entregue'];$current=$order?array_search((string)$order['status'],$steps,true):false;if($current===false)$current=0;
?>
<section class="section page-shell client-page client-area"><div class="container client-shell">
<?php $clientActive='pedidos';require __DIR__.'/../includes/cliente-nav.php'; ?>
<div class="client-content">
<?php if(!$order): ?><div class="client-empty"><div class="client-empty-icon">!</div><h3>Pedido não encontrado</h3><p>O pedido solicitado não está disponível para esta conta.</p><a class="btn btn-bordo" href="<?=url('cliente/meus-pedidos.php')?>">VOLTAR AOS PEDIDOS</a></div>
<?php else: ?>
<div class="client-status-hero"><span class="eyebrow">Pedido #<?=e($order['id'])?></span><h1><?=e(statusLabel($order['status']))?></h1><p>Criado em <?=formatarData($order['criado_em'])?> • <?=formatarMoeda((float)$order['total'])?></p><div class="client-big-tracker"><?php foreach($steps as $idx=>$step): ?><div class="client-big-step <?=$idx<$current?'done':''?> <?=$idx===$current?'current':''?>"><div class="client-big-dot">✓</div><div class="client-big-label"><?=e(statusLabel($step))?></div></div><?php endforeach; ?></div></div>
<div class="client-detail-layout"><div>
<section class="client-detail-card"><h2>Itens do pedido</h2><?php if(!$items): ?><div class="client-empty"><div class="client-empty-icon">▣</div><h3>Nenhum item encontrado</h3><p>O pedido foi localizado, mas seus itens não puderam ser carregados.</p></div><?php else: foreach($items as $item): $meta='';$note='';$json=json_decode((string)$item['personalizacao_json'],true);if(is_array($json)&&!empty($json['porcoes'])&&is_array($json['porcoes'])){$parts=[];foreach($json['porcoes'] as $i=>$portion){$part='Porção '.((int)$i+1).': '.(($portion['tipo']??'')==='barca'?'Barca ':'').(string)($portion['tamanho']??'');if(!empty($portion['opcoes'])&&is_array($portion['opcoes']))$part.=' — '.implode(', ',array_map(fn($op)=>(string)($op['nome']??''),$portion['opcoes']));$parts[]=$part;}$meta=implode(' | ',$parts);}elseif(is_array($json)&&!empty($json['opcoes']))$meta=implode(', ',array_map(fn($op)=>(string)($op['nome']??''),$json['opcoes']));if(is_array($json))$note=trim((string)($json['observacao']??'')); ?><div class="client-item-row client-item-row-note"><div class="client-item-img"><img src="<?=asset('img/customizador.svg')?>" alt=""></div><div><strong><?=e($item['nome_produto'])?></strong><span>Qtd. <?=e($item['quantidade'])?><?= $meta ? ' • '.e($meta) : ''?></span><?php if($note):?><small class="client-item-note"><strong>Observação:</strong> <?=e($note)?></small><?php endif;?></div><div class="client-item-price"><?=formatarMoeda((float)$item['subtotal'])?></div></div><?php endforeach; endif; ?></section>
<section class="client-detail-card"><h2>Entrega</h2><p class="client-address"><strong><?=e($order['nome_cliente'])?></strong><br><?=e($order['rua'])?>, <?=e($order['numero'])?><?=!empty($order['complemento'])?', '.e($order['complemento']):''?><br><?=e($order['bairro'])?> — <?=e($order['cidade'])?><br>CEP <?=e($order['cep'])?></p><div class="client-note-list"><div><strong>Observação do pedido</strong><span><?=trim((string)($order['observacao']??''))!=='' ? e((string)$order['observacao']) : 'Nenhuma observação geral.'?></span></div><div><strong>Observação da entrega</strong><span><?=trim((string)($order['observacao_entrega']??''))!=='' ? e((string)$order['observacao_entrega']) : 'Nenhuma observação de entrega.'?></span></div></div></section>
</div><aside>
<section class="client-detail-card"><h2>Resumo</h2><div class="client-summary"><div class="client-summary-row"><span>Subtotal</span><strong><?=formatarMoeda((float)$order['total'])?></strong></div><div class="client-summary-row"><span>Pagamento</span><strong><?=e(ucfirst($order['forma_pagamento']))?> na entrega</strong></div><div class="client-summary-total"><span>Total</span><strong><?=formatarMoeda((float)$order['total'])?></strong></div></div></section>
<section class="client-detail-card"><h2>Precisa de ajuda?</h2><p class="client-help-text">Fale com a Açaí Flow para tirar dúvidas sobre seu pedido.</p><a class="btn btn-bordo client-help-btn" href="<?=url('contato.php')?>">FALE COM A GENTE</a></section>
</aside></div>
<?php endif; ?>
</div></div></section>
<?php require __DIR__.'/../includes/footer.php'; ?>
