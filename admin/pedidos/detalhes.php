<?php
declare(strict_types=1);
require_once __DIR__.'/../../config/seguranca.php';
exigirAdmin();
$pageTitle='Detalhes do pedido';
require __DIR__.'/../_header.php';
$pdo=getPDO();
$id=(int)($_GET['id']??0);
$order=null;$items=[];$itemNotes=[];$error='';
if($pdo){
    try{
        $st=$pdo->prepare('SELECT * FROM pedidos WHERE id=? LIMIT 1');
        $st->execute([$id]);
        $order=$st->fetch();
        if($order){
            $st=$pdo->prepare('SELECT * FROM itens_pedido WHERE pedido_id=? ORDER BY id');
            $st->execute([$id]);
            $items=$st->fetchAll();
            foreach($items as $item){
                $data=json_decode((string)($item['personalizacao_json']??''),true);
                if(is_array($data) && trim((string)($data['observacao']??''))!==''){
                    $itemNotes[]=['produto'=>$item['nome_produto'],'observacao'=>trim((string)$data['observacao'])];
                }
            }
        }
    }catch(Throwable $e){
        error_log('[Açai Flow] Detalhes do pedido: '.$e->getMessage());
        $error='Não foi possível carregar este pedido.';
    }
}
?>
<div class="admin-heading">
  <div><h1>Pedido #<?=e($id)?></h1><?php if($order):?><p><?=e($order['nome_cliente'])?> · <?=formatarMoeda((float)$order['total'])?></p><?php endif;?></div>
  <a class="admin-btn secondary" href="<?=url('admin/pedidos/index.php')?>">← Pedidos</a>
</div>
<?php if($error):?><div class="admin-card admin-alert-card"><?=e($error)?></div>
<?php elseif(!$order):?><div class="admin-card"><div class="empty-state">Pedido não encontrado.</div></div>
<?php else:?>
<div class="dashboard-grid">
<section class="admin-card">
  <div class="admin-card-head"><div><h2>Itens do pedido</h2><p>Composição enviada pelo cliente.</p></div><span class="status-pill <?=statusClasse($order['status'])?>"><?=e(statusLabel($order['status']))?></span></div>
  <div class="order-items">
  <?php foreach($items as $item):
      $data=json_decode((string)($item['personalizacao_json']??''),true);
      $opts=is_array($data) && isset($data['opcoes']) && is_array($data['opcoes']) ? $data['opcoes'] : [];
  ?>
    <div class="order-item admin-order-item">
      <div class="order-item-thumb"><img src="<?=asset('img/customizador.svg')?>" alt=""></div>
      <div>
        <div class="order-item-name"><?=e($item['nome_produto'])?></div>
        <div class="order-item-meta">Quantidade <?=e($item['quantidade'])?></div>
        <?php if($opts):?><div class="admin-item-options"><?php foreach($opts as $op):?><span><?=e((string)($op['nome']??''))?></span><?php endforeach;?></div><?php endif;?>
        <?php if(is_array($data) && trim((string)($data['observacao']??''))!==''):?><div class="admin-item-note"><strong>Observação do pedido:</strong> <?=e((string)$data['observacao'])?></div><?php endif;?>
      </div>
      <div class="order-item-price"><?=formatarMoeda((float)$item['subtotal'])?></div>
    </div>
  <?php endforeach;?>
  </div>
</section>
<section class="admin-card">
  <h2>Atualizar status</h2>
  <form action="<?=url('processa/atualizar-pedido.php')?>" method="post">
    <input type="hidden" name="csrf" value="<?=csrfToken()?>">
    <input type="hidden" name="pedido_id" value="<?=$order['id']?>">
    <div class="form-group"><label>Status</label><select class="form-control" name="status"><?php foreach(['recebido','confirmado','preparando','pronto','saiu_entrega','entregue','cancelado'] as $s):?><option value="<?=$s?>" <?=$order['status']===$s?'selected':''?>><?=e(statusLabel($s))?></option><?php endforeach;?></select></div>
    <button class="admin-btn" style="margin-top:14px">ATUALIZAR PEDIDO</button>
  </form>
  <hr style="border:0;border-top:1px solid var(--borda);margin:20px 0">
  <h2>Entrega</h2>
  <p style="font-size:.7rem;line-height:1.8"><?=e($order['rua'])?>, <?=e($order['numero'])?><br><?=e($order['bairro'])?> — <?=e($order['cidade'])?><br>CEP <?=e($order['cep'])?><br><strong>Pagamento:</strong> <?=e(ucfirst($order['forma_pagamento']))?> na entrega</p>
  <div class="admin-note-block"><strong>Observação da entrega</strong><p><?=trim((string)($order['observacao_entrega']??''))!=='' ? e((string)$order['observacao_entrega']) : 'Nenhuma observação informada.'?></p></div>
  <div class="admin-note-block"><strong>Observação geral do pedido</strong><p><?=trim((string)($order['observacao']??''))!=='' ? e((string)$order['observacao']) : 'Nenhuma observação geral informada.'?></p></div>
  <?php if($order['complemento']):?><div class="admin-note-block"><strong>Complemento</strong><p><?=e($order['complemento'])?></p></div><?php endif;?>
</section>
</div>
<?php endif;?>
<?php require __DIR__.'/../_footer.php'; ?>
