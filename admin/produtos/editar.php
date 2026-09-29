<?php
require_once __DIR__.'/../../config/seguranca.php';
require_once __DIR__.'/../../config/catalogo.php';
exigirAdmin();
$pageTitle='Editar produto';
require __DIR__.'/../_header.php';
$pdo=getPDO();
$id=(int)($_GET['id']??0);
$p=null;$cats=[];
if($pdo){
    $st=$pdo->prepare('SELECT * FROM produtos WHERE id=?');
    $st->execute([$id]);
    $p=$st->fetch();
    $cats=tableExists($pdo,'categorias')?$pdo->query('SELECT id,nome FROM categorias WHERE ativo=1 ORDER BY ordem,nome')->fetchAll():[];
}
$hasOfferSchedule=$pdo&&colunaExiste($pdo,'produtos','oferta_inicio')&&colunaExiste($pdo,'produtos','oferta_fim');
$isComboCategory=$p && $pdo && tableExists($pdo,'categorias') ? mb_strtolower((string)$pdo->query("SELECT nome FROM categorias WHERE id=".(int)$p['categoria_id'])->fetchColumn())==='combos' : false;
$comboConfigAdmin=$p&&$isComboCategory?getComboConfiguracao($pdo,(int)$p['id'],$p):null;
$adminComboSizes=$pdo?getMonteTamanhos($pdo):[];
$inicioInput=$hasOfferSchedule&&!empty($p['oferta_inicio'])?date('Y-m-d\\TH:i',strtotime((string)$p['oferta_inicio'])):'';
$fimInput=$hasOfferSchedule&&!empty($p['oferta_fim'])?date('Y-m-d\\TH:i',strtotime((string)$p['oferta_fim'])):'';
?>
<div class="admin-heading"><div><h1>Editar produto</h1><p>Atualize informações, preço, estoque e programação no cardápio.</p></div><a class="admin-btn secondary" href="<?=url('admin/produtos/index.php')?>">← Voltar</a></div>
<?php if(!$p):?>
<div class="admin-card"><div class="empty-state">Produto não encontrado.</div></div>
<?php else:?>
<div class="admin-card">
<form action="<?=url('admin/produtos/atualizar.php')?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf" value="<?=csrfToken()?>"><input type="hidden" name="id" value="<?=$p['id']?>">
<div class="form-grid">
    <div class="form-group full"><label>Nome</label><input class="form-control" name="nome" required value="<?=e($p['nome'])?>"></div>
    <div class="form-group"><label>Categoria</label><select class="form-control" name="categoria_id" required data-category-select><?php foreach($cats as $c):?><option value="<?=$c['id']?>" <?=$c['id']==$p['categoria_id']?'selected':''?>><?=e($c['nome'])?></option><?php endforeach;?></select></div>
    <div class="form-group"><label>Preço</label><?php $isComboCasalEdit = mb_strtolower(trim((string)$p['nome'])) === 'combo casal'; ?><input class="form-control" type="number" step="0.01" min="0" name="preco" value="<?=e($p['preco'])?>" <?=$isComboCasalEdit?'readonly':''?> required><?php if($isComboCasalEdit): ?><div class="form-help">O preço do Combo Casal é calculado conforme os dois copos/barcas escolhidos.</div><?php endif; ?></div>
    <div class="form-group"><label>Estoque</label><input class="form-control" type="number" min="0" name="estoque" value="<?=e($p['estoque'])?>" required></div>
    <div class="form-group"><label>Nova imagem</label><input class="form-control" type="file" name="imagem" accept="image/png,image/jpeg,image/webp"></div>
    <div class="form-group full"><label>Descrição</label><textarea class="form-control" name="descricao"><?=e($p['descricao'])?></textarea></div>
    <div class="form-group"><label>Destaque</label><label class="checkbox-row"><input type="checkbox" name="destaque" value="1" <?=$p['destaque']?'checked':''?>> Mostrar na Home</label></div>
    <div class="form-group"><label>Promoção</label><label class="checkbox-row"><input type="checkbox" name="promocao" value="1" <?=!empty($p['promocao'])?'checked':''?>> Marcar como promoção</label></div>
    <div class="form-group"><label>Ativo</label><label class="checkbox-row"><input type="checkbox" name="ativo" value="1" <?=$p['ativo']?'checked':''?>> Produto disponível</label></div>
</div>

<div class="admin-offer-settings">
    <div class="admin-offer-settings-head"><div><h2>Exibição especial no cardápio</h2><p>Defina um período opcional para este combo ou promoção.</p></div><span class="admin-offer-hint"><?= $hasOfferSchedule ? 'Agendamento disponível' : 'Migração do banco necessária' ?></span></div>
    <div class="form-grid">
        <div class="form-group"><label>Início da exibição</label><input class="form-control" type="datetime-local" name="oferta_inicio" value="<?=e($inicioInput)?>" <?= $hasOfferSchedule ? '' : 'disabled' ?>></div>
        <div class="form-group"><label>Fim da exibição</label><input class="form-control" type="datetime-local" name="oferta_fim" value="<?=e($fimInput)?>" <?= $hasOfferSchedule ? '' : 'disabled' ?>></div>
    </div>
    <p class="form-help">Deixe as datas em branco para exibição contínua. Para um combo, use a categoria <strong>Combos</strong>; para uma promoção, marque <strong>Promoção</strong>.</p>
</div>

<?php $comboConfigAdmin=$comboConfigAdmin; $adminComboSizes=$adminComboSizes; include __DIR__.'/_combo_config.php'; ?>

<button class="admin-btn" style="margin-top:16px">ATUALIZAR PRODUTO</button>
</form>
</div>
<?php endif;?>
<?php require __DIR__.'/../_footer.php'; ?>
