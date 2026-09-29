<?php
declare(strict_types=1);

$pageTitle = 'Cardápio';
$activePage = 'cardapio';
require __DIR__ . '/includes/header.php';
require_once __DIR__ . '/config/limites.php';
require_once __DIR__ . '/config/catalogo.php';

$pdo = getPDO();
$sizes = getMonteTamanhos($pdo);

$copoCount = 0;
$barcaCount = 0;
foreach ($sizes as &$size) {
    [$f,$c,$a,$ca,$ac] = limitesPersonalizacao((string)$size['tipo'], (string)$size['nome']);
    $size['limites'] = compact('f','c','a','ca','ac');
    if ($size['tipo'] === 'copo') $copoCount++; else $barcaCount++;
}
unset($size);

/**
 * Combos e promoções são produtos normais e aparecem aqui somente quando
 * estiverem ativos e dentro do período programado no painel.
 */
$specialOffers = getCatalogoOfertas($pdo);

// Liga os cartões de tamanho aos produtos reais cadastrados no MySQL.
// Assim o botão “Ver item” abre a mesma página de detalhe usada pelos combos e promoções.
$sizeProducts = [];
if ($pdo && tableExists($pdo, 'produtos')) {
    try {
        $rows = $pdo->query("SELECT id,nome,descricao,preco,estoque,imagem,ativo FROM produtos WHERE ativo=1 ORDER BY id")->fetchAll();
        foreach ($rows as $row) {
            $sizeProducts[mb_strtolower(trim((string)$row['nome']))] = $row;
        }
    } catch (Throwable $e) {
        $sizeProducts = [];
    }
}

function cardapioImagem(array $size, array $sizeProducts = []): string
{
    $mapped = imagemTamanhoCatalogo((string)$size['tipo'], (string)$size['nome']);
    if ($mapped && !str_ends_with($mapped, 'placeholder.svg')) return $mapped;
    $label = mb_strtolower(trim($size['tipo'] === 'barca' ? 'Barca ' . $size['nome'] : 'Açaí ' . $size['nome']));
    if (!empty($sizeProducts[$label]['imagem'])) {
        return produtoImagem($sizeProducts[$label]['imagem']);
    }
    return $size['tipo'] === 'barca'
        ? asset('img/produtos/acai-barca-real.jpg')
        : asset('img/produtos/acai-copo-real-2x.png');
}

function cardapioTitulo(array $size): string
{
    return $size['tipo'] === 'barca' ? 'Barca ' . $size['nome'] : 'Açaí ' . $size['nome'];
}

function ofertaImagem(array $offer): string
{
    return produtoImagem($offer['imagem'] ?? null);
}

?>

<section class="page-hero catalog-hero">
    <div class="catalog-floating-fruits" aria-hidden="true">
        <span class="catalog-floating-fruit cff-1"><img src="<?=asset('img/animacoes/morango.svg')?>" alt=""></span>
        <span class="catalog-floating-fruit cff-2"><img src="<?=asset('img/animacoes/banana.svg')?>" alt=""></span>
        <span class="catalog-floating-fruit cff-3"><img src="<?=asset('img/animacoes/mirtilo.svg')?>" alt=""></span>
        <span class="catalog-floating-fruit cff-4"><img src="<?=asset('img/animacoes/morango.svg')?>" alt=""></span>
    </div>
    <div class="container">
        <span class="eyebrow">AÇAÍ EXPRESSO</span>
        <h1>Nosso <span>Cardápio.</span></h1>
        <p>Escolha um copo ou uma barca e monte sua combinação na experiência Açaí Flow.</p>
    </div>
</section>

<?php if ($specialOffers): ?>
<section class="section catalog-offers-section">
    <div class="container">
        <div class="catalog-heading">
            <div>
                <span class="eyebrow">POR TEMPO LIMITADO</span>
                <h2 class="section-title catalog-title">Combos e <span class="script">promoções.</span></h2>
            </div>
            <p class="catalog-heading-text">Os itens desta área são controlados pelo painel administrativo e aparecem somente enquanto estiverem ativos dentro do período definido.</p>
        </div>

        <div class="catalog-offers-grid">
            <?php foreach ($specialOffers as $offer):
                $isCombo = mb_strtolower((string)($offer['categoria'] ?? '')) === 'combos';
                $offerLabel = $isCombo ? 'COMBO' : 'PROMOÇÃO';
            ?>
                <article class="catalog-offer-card">
                    <div class="catalog-offer-media">
                        <img src="<?=e(ofertaImagem($offer))?>" alt="<?=e($offer['nome'])?>" loading="lazy" decoding="async">
                        <span class="catalog-offer-badge <?= $isCombo ? 'is-combo' : '' ?>"><?=e($offerLabel)?></span>
                    </div>
                    <div class="catalog-offer-content">
                        <div>
                            <h3><?=e($offer['nome'])?></h3>
                            <?php if (!empty($offer['descricao'])): ?>
                                <p><?=e($offer['descricao'])?></p>
                            <?php endif; ?>
                        </div>
                        <div class="catalog-offer-bottom">
                            <strong><?=mb_strtolower(trim((string)$offer['nome'])) === 'combo casal' ? 'Preço conforme escolhas' : formatarMoeda((float)$offer['preco'])?></strong>
                            <a class="btn btn-bordo btn-small" href="<?=url('produto.php?id='.(int)$offer['id'])?>">VER ITEM →</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="section catalog-section">
    <div class="catalog-fruit-trail" aria-hidden="true"><span><img src="<?=asset('img/animacoes/banana.svg')?>" alt=""></span><span><img src="<?=asset('img/animacoes/mirtilo.svg')?>" alt=""></span></div>
    <div class="container">
        <div class="catalog-heading">
            <div>
                <span class="eyebrow">ESCOLHA SEU FORMATO</span>
                <h2 class="section-title catalog-title">Tamanhos que <span class="script">existem no Flow.</span></h2>
            </div>
            <p class="catalog-heading-text">O cardápio desta página usa exatamente os tamanhos disponíveis no <strong>Monte seu açaí</strong>.</p>
        </div>

        <div class="filter-bar catalog-filters" role="tablist" aria-label="Filtrar cardápio">
            <button class="filter-btn active" type="button" data-filter="todos" aria-selected="true">Todos</button>
            <button class="filter-btn" type="button" data-filter="copo" aria-selected="false">Copos</button>
            <button class="filter-btn" type="button" data-filter="barca" aria-selected="false">Barcas</button>
        </div>

        <div class="catalog-grid catalog-grid-flow">
            <?php foreach ($sizes as $size): ?>
                <?php
                $isBarca = $size['tipo'] === 'barca';
                $label = cardapioTitulo($size);
                ?>
                <?php
                $productKey = mb_strtolower(trim($label));
                $linkedProduct = $sizeProducts[$productKey] ?? null;
                $detailHref = $linkedProduct
                    ? url('produto.php?id='.(int)$linkedProduct['id'])
                    : url('monte-seu-acai.php?tipo='.rawurlencode((string)$size['tipo']).'&tamanho='.(int)$size['id']);
                ?>
                <article class="catalog-product-card" data-category="<?=e($size['tipo'])?>">
                    <div class="catalog-product-media">
                        <img src="<?=e(cardapioImagem($size, $sizeProducts))?>" alt="<?=e($label)?>" loading="lazy" decoding="async">
                    </div>
                    <div class="catalog-product-content">
                        <span class="catalog-product-type"><?= $isBarca ? 'BARCA' : 'COPO' ?></span>
                        <h3><?=e($label)?></h3>
                        <div class="catalog-product-bottom">
                            <strong><?=formatarMoeda((float)$size['preco'])?></strong>
                            <a class="btn btn-bordo btn-small" href="<?=e($detailHref)?>">VER ITEM →</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="catalog-note">
            <strong><?=e((string)($copoCount + $barcaCount))?> opções disponíveis.</strong>
            <span>Os limites de frutas, cremes, adicionais, caldas e acompanhamentos são definidos automaticamente no montador.</span>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
