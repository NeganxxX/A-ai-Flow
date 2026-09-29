<?php
$pageTitle='Finalizar pedido';
require_once __DIR__.'/config/seguranca.php';
exigirLogin();
require __DIR__.'/includes/header.php';
$pdo=getPDO();
$cliente=['nome'=>nomeCliente(),'email'=>'','telefone'=>''];
if($pdo && tableExists($pdo,'usuarios')) { $st=$pdo->prepare('SELECT nome,email,telefone FROM usuarios WHERE id=?');$st->execute([$_SESSION['cliente_id']]);$cliente=$st->fetch()?:$cliente; }
$pix=obterConfiguracao($pdo,'pix_chave','Chave Pix ainda não configurada');
?>
<section class="page-hero"><div class="container"><span class="eyebrow">Açaí Flow</span><h1>Finalize seu <span>pedido.</span></h1><p>Confirme seus dados e escolha como deseja pagar no momento da entrega.</p></div></section>
<section class="section"><div class="container checkout-layout">
<div class="checkout-panel">
<h2>Itens do pedido</h2><div id="checkoutCart" class="order-items"><div class="loading-state">Carregando carrinho…</div></div>
<h2 style="margin-top:28px">Entrega</h2>
<form id="checkoutForm" action="<?=url('processa/pedido.php')?>" method="post">
<input type="hidden" name="csrf" value="<?=csrfToken()?>">
<div class="form-grid"><div class="form-group"><label>Nome</label><input class="form-control" value="<?=e($cliente['nome'])?>" readonly></div><div class="form-group"><label>Telefone</label><input class="form-control" name="telefone" required value="<?=e($cliente['telefone']??'')?>"></div><div class="form-group"><label>CEP</label><input class="form-control" name="cep" required maxlength="9" placeholder="00000-000"></div><div class="form-group"><label>Cidade</label><input class="form-control" name="cidade" required value="Manacapuru"></div><div class="form-group full"><label>Rua</label><input class="form-control" name="rua" required></div><div class="form-group"><label>Número</label><input class="form-control" name="numero" required></div><div class="form-group"><label>Complemento</label><input class="form-control" name="complemento"></div><div class="form-group"><label>Bairro</label><input class="form-control" name="bairro" required></div><div class="form-group full"><label>Observação do pedido</label><textarea class="form-control" name="observacao" maxlength="1000" placeholder="Ex.: retirar algum ingrediente ou outra preferência do pedido."></textarea></div><div class="form-group full"><label>Observação da entrega</label><textarea class="form-control" name="observacao_entrega" maxlength="1000" placeholder="Ex.: tocar a campainha, deixar na portaria, referência do endereço..."></textarea></div></div>
<h2 style="margin-top:28px">Pagamento na entrega</h2>
<div class="pay-options">
<?php foreach([['dinheiro','Dinheiro'],['pix','Pix'],['credito','Cartão de crédito'],['debito','Cartão de débito']] as $pay): ?><div class="pay-option"><input type="radio" id="pay<?=$pay[0]?>" name="forma_pagamento" value="<?=$pay[0]?>" required><label for="pay<?=$pay[0]?>"><?=$pay[1]?><small style="display:block;color:var(--muted);font-weight:500">na entrega</small></label></div><?php endforeach; ?>
</div>
<div id="cashChange" class="pix-box hide"><strong>Troco</strong><input class="form-control" name="troco_para" type="number" step="0.01" min="0" placeholder="Troco para quanto?"></div>
<div id="pixPaymentInfo" class="pix-box hide"><strong>Pix da Açaí Flow</strong><span>O pagamento será feito no ato da entrega.</span><span>Chave: <span class="pix-key"><?=e($pix)?></span></span></div>
<button type="submit" class="btn btn-bordo" style="width:100%;margin-top:20px">CONFIRMAR PEDIDO →</button>
</form></div>
<aside class="checkout-panel"><h2>Resumo</h2><div class="notice">O preço exibido no navegador é apenas informativo. O PHP recalcula o pedido usando o banco de dados antes de registrar a compra.</div><div class="checkout-total"><div class="line"><span>Subtotal</span><span id="checkoutTotal">R$ 0,00</span></div><div class="line"><span>Entrega</span><span>A combinar</span></div><div class="line total"><span>Total</span><span id="checkoutTotalAside">R$ 0,00</span></div></div></aside>
</div></section>
<?php $extraJs=['js/pedido.js']; require __DIR__.'/includes/footer.php'; ?>
