<?php
declare(strict_types=1);
require_once __DIR__.'/config/conexao.php';
require_once __DIR__.'/includes/funcoes.php';
require_once __DIR__.'/config/catalogo.php';

$pdo = getPDO();
$id = (int)($_GET['id'] ?? 0);
$sizeId = (int)($_GET['tamanho'] ?? 0);
$p = null;
$sizeProduct = null;

if ($pdo && $id > 0 && tableExists($pdo, 'produtos')) {
    try {
        $st = $pdo->prepare('SELECT p.*,c.nome categoria_nome FROM produtos p LEFT JOIN categorias c ON c.id=p.categoria_id WHERE p.id=? AND p.ativo=1 LIMIT 1');
        $st->execute([$id]);
        $p = $st->fetch() ?: null;
    } catch (Throwable $e) {
        $p = null;
    }
}

// O cardápio de copos/barcas pode abrir esta mesma página pelo ID do tamanho.
if (!$p && $sizeId > 0 && $pdo) {
    try {
        $st = $pdo->prepare('SELECT id,tipo,nome,preco,volume_ml FROM tamanhos WHERE id=? AND ativo=1 LIMIT 1');
        $st->execute([$sizeId]);
        $sizeProduct = $st->fetch() ?: null;
    } catch (Throwable $e) {
        $sizeProduct = null;
    }
    if ($sizeProduct) {
        $label = $sizeProduct['tipo'] === 'barca' ? 'Barca '.$sizeProduct['nome'] : 'Açaí '.$sizeProduct['nome'];
        $pageTitle = $label;
    }
} else {
    $pageTitle = $p ? $p['nome'] : 'Produto';
}

$activePage = 'cardapio';
require __DIR__.'/includes/header.php';

$isCombo = $p && mb_strtolower(trim((string)($p['categoria_nome'] ?? ''))) === 'combos';
$isComboCasal = $isCombo && mb_strtolower(trim((string)$p['nome'])) === 'combo casal';
?>
<section class="page-shell section">
  <div class="container">
    <div class="breadcrumb"><a href="<?=url('cardapio.php')?>">Cardápio</a><span>›</span><span><?=e($pageTitle)?></span></div>

    <?php if (!$p && !$sizeProduct): ?>
      <div class="empty-state">
        <h2>Produto não encontrado.</h2>
        <br>
        <a class="btn btn-bordo btn-small" href="<?=url('cardapio.php')?>">VOLTAR AO CARDÁPIO</a>
      </div>
    <?php elseif ($sizeProduct): ?>
      <?php
      $label = $sizeProduct['tipo'] === 'barca' ? 'Barca '.$sizeProduct['nome'] : 'Açaí '.$sizeProduct['nome'];
      $image = imagemTamanhoCatalogo((string)$sizeProduct['tipo'], (string)$sizeProduct['nome']);
      if (str_ends_with($image, 'placeholder.svg')) { $image = $sizeProduct['tipo'] === 'barca' ? asset('img/produtos/acai-barca-real.jpg') : asset('img/produtos/acai-copo-real-2x.png'); }
      ?>
      <div class="product-detail">
        <div class="product-detail-image product-detail-image-zoom"><img src="<?=e($image)?>" alt="<?=e($label)?>" loading="eager"></div>
        <div class="product-detail-copy">
          <span class="tag"><?=e($sizeProduct['tipo'] === 'barca' ? 'Barca' : 'Copo')?></span>
          <h1 style="margin-top:10px"><?=e($label)?></h1>
          <div class="price"><?=formatarMoeda((float)$sizeProduct['preco'])?></div>
          <p class="text-muted">Escolha este tamanho no Monte seu açaí e personalize sua combinação com as opções disponíveis.</p>
          <?php if (!empty($sizeProduct['volume_ml'])): ?><div class="detail-meta"><span><?=e((int)$sizeProduct['volume_ml'])?> ml de açaí</span></div><?php endif; ?>
          <div class="detail-box">
            <p class="text-muted" style="font-size:.75rem">As opções e limites são aplicados automaticamente conforme o tamanho selecionado.</p>
            <div class="action-row" style="margin-top:15px">
              <a class="btn btn-bordo" href="<?=url('monte-seu-acai.php?tipo='.rawurlencode((string)$sizeProduct['tipo']).'&tamanho='.(int)$sizeProduct['id'])?>">MONTE ESTE ITEM →</a>
              <a class="btn btn-outline" href="<?=url('cardapio.php')?>">VOLTAR</a>
            </div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <?php $stock=(int)$p['estoque']; ?>
      <div class="product-detail">
        <?php $publicProductImage = imagemProdutoPublica((string)$p['nome'], $p['imagem'] ?? null); ?>
      <div class="product-detail-image product-detail-image-zoom"><img src="<?=e($publicProductImage)?>" alt="<?=e($p['nome'])?>" loading="eager"></div>
        <div class="product-detail-copy">
          <span class="tag"><?=e($p['categoria_nome']??'Açaí Flow')?></span>
          <h1 style="margin-top:10px"><?=e($p['nome'])?></h1>
          <?php if ($isComboCasal): ?>
            <div class="price dynamic-price-label">Preço conforme escolhas</div>
          <?php else: ?>
            <div class="price"><?=formatarMoeda((float)$p['preco'])?></div>
          <?php endif; ?>
          <p class="text-muted"><?=e($p['descricao']??'')?></p>
          <div style="margin-top:14px"><span class="stock <?=$stock<=0?'out':($stock<=3?'low':'')?>"><?=$stock<=0?'Esgotado':($stock<=3?'Últimas unidades':'Disponível')?></span></div>
          <div class="detail-box">
            <p class="text-muted" style="font-size:.75rem"><?=e($isCombo ? ($isComboCasal ? 'No Combo Casal, o total é formado pela soma dos preços dos dois copos/barcas escolhidos, mais eventuais adicionais pagos.' : 'Este combo é montável: escolha cada porção e personalize as opções permitidas.') : 'Quer escolher frutas, cremes, adicionais e caldas? Use o montador para criar uma combinação personalizada.')?></p>
            <div class="action-row" style="margin-top:15px">
              <?php if($stock>0 && !$isCombo): ?>
                <div class="quantity"><button type="button" id="productMinus">−</button><input id="productQuantity" type="number" min="1" max="<?=max(1,min(20,$stock))?>" value="1"><button type="button" id="productPlus">+</button></div>
                <button type="button" class="btn btn-bordo" id="addProductBtn" data-add-cart data-product-id="<?=e((string)$p['id'])?>" data-name="<?=e($p['nome'])?>" data-price="<?=e((string)$p['preco'])?>" data-image="<?=e($publicProductImage)?>">ADICIONAR AO CARRINHO →</button>
              <?php endif; ?>
              <?php if($isCombo): ?><a class="btn btn-bordo" href="<?=url('monte-seu-acai.php?combo='.(int)$p['id'])?>">MONTAR COMBO →</a><?php else: ?><a class="btn btn-outline" href="<?=url('monte-seu-acai.php')?>">MONTAR DO MEU JEITO →</a><?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>
<script>
document.addEventListener('DOMContentLoaded',()=>{
  const input=document.getElementById('productQuantity');
  const minus=document.getElementById('productMinus');
  const plus=document.getElementById('productPlus');
  if(input){minus?.addEventListener('click',()=>input.value=Math.max(1,Number(input.value)-1));plus?.addEventListener('click',()=>input.value=Math.min(Number(input.max||20),Number(input.value)+1));const btn=document.getElementById('addProductBtn');btn?.addEventListener('click',()=>{btn.dataset.quantity=input.value;});}
});
</script>
<?php require __DIR__.'/includes/footer.php'; ?>
