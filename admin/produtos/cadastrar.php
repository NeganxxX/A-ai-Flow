<?php
require_once __DIR__.'/../../config/seguranca.php';
require_once __DIR__.'/../../config/catalogo.php';
exigirAdmin();
$pageTitle='Novo produto';
require __DIR__.'/../_header.php';
$pdo=getPDO();
$cats=$pdo&&tableExists($pdo,'categorias')?$pdo->query('SELECT id,nome FROM categorias WHERE ativo=1 ORDER BY ordem,nome')->fetchAll():[];
$categoriaPreSelecionada=(int)($_GET['categoria']??0);
$hasOfferSchedule=$pdo&&colunaExiste($pdo,'produtos','oferta_inicio')&&colunaExiste($pdo,'produtos','oferta_fim');
$comboConfigAdmin=null;
$adminComboSizes=$pdo?getMonteTamanhos($pdo):[];
?>
<div class="admin-heading">
    <div><h1>Novo produto</h1><p>Adicione um item ao cardápio.</p></div>
    <a class="admin-btn secondary" href="<?=url('admin/produtos/index.php')?>">← Voltar</a>
</div>
<div class="admin-card">
<form action="<?=url('admin/produtos/salvar.php')?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf" value="<?=csrfToken()?>">
<div class="form-grid">
    <div class="form-group full"><label>Nome</label><input class="form-control" name="nome" required maxlength="120"></div>
    <div class="form-group"><label>Categoria</label><select class="form-control" name="categoria_id" required data-category-select><option value="">Selecione</option><?php foreach($cats as $c):?><option value="<?=$c['id']?>" <?=$categoriaPreSelecionada===(int)$c['id']?'selected':''?>><?=e($c['nome'])?></option><?php endforeach;?></select></div>
    <div class="form-group"><label>Preço</label><input class="form-control" type="number" step="0.01" min="0" name="preco" required></div>
    <div class="form-group"><label>Estoque</label><input class="form-control" type="number" min="0" name="estoque" value="0" required></div>
    <div class="form-group"><label>Imagem</label><input class="form-control" type="file" name="imagem" accept="image/png,image/jpeg,image/webp"></div>
    <div class="form-group full"><label>Descrição</label><textarea class="form-control" name="descricao"></textarea></div>
    <div class="form-group"><label>Destaque</label><label class="checkbox-row"><input type="checkbox" name="destaque" value="1"> Mostrar na Home</label></div>
    <div class="form-group"><label>Promoção</label><label class="checkbox-row"><input type="checkbox" name="promocao" value="1"> Marcar como promoção</label></div>
</div>

<div class="admin-offer-settings">
    <div class="admin-offer-settings-head"><div><h2>Exibição especial no cardápio</h2><p>Use para programar quando um combo ou promoção deve aparecer na área de destaque.</p></div><span class="admin-offer-hint"><?= $hasOfferSchedule ? 'Agendamento disponível' : 'Migração do banco necessária' ?></span></div>
    <div class="form-grid">
        <div class="form-group"><label>Início da exibição</label><input class="form-control" type="datetime-local" name="oferta_inicio" <?= $hasOfferSchedule ? '' : 'disabled' ?>></div>
        <div class="form-group"><label>Fim da exibição</label><input class="form-control" type="datetime-local" name="oferta_fim" <?= $hasOfferSchedule ? '' : 'disabled' ?>></div>
    </div>
    <p class="form-help">Deixe as duas datas em branco para manter o combo/promoção sempre visível enquanto o produto estiver ativo. Para combos, selecione a categoria <strong>Combos</strong>.</p>
</div>

<?php include __DIR__.'/_combo_config.php'; ?>

<button class="admin-btn" style="margin-top:16px">SALVAR PRODUTO</button>
</form>
</div>
<?php require __DIR__.'/../_footer.php'; ?>
