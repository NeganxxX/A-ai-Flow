<?php
declare(strict_types=1);
$pageTitle = 'Monte seu açaí';
$activePage = 'monte';
require __DIR__.'/includes/header.php';
require_once __DIR__.'/config/limites.php';
require_once __DIR__.'/config/catalogo.php';
$pdo = getPDO();

$sizes = [];
$options = [];
$specialOffers = getCatalogoOfertas($pdo);
if ($pdo && tableExists($pdo,'tamanhos')) {
    try {
        $sizes = $pdo->query("SELECT id,tipo,nome,preco,volume_ml,limite_frutas,limite_cremes,limite_adicionais,limite_caldas,limite_acompanhamentos FROM tamanhos WHERE ativo=1 ORDER BY tipo,ordem,id")->fetchAll();
    } catch (Throwable $e) {
        try {
            $legacy = $pdo->query("SELECT id,tipo,nome,preco FROM tamanhos WHERE ativo=1 ORDER BY tipo,ordem,id")->fetchAll();
            foreach ($legacy as $row) {
                $isCup = $row['tipo'] === 'copo';
                $cupLimits = ['180ml'=>[1,1,1,1,1],'200ml'=>[1,1,1,1,2],'300ml'=>[1,1,1,1,2],'400ml'=>[1,1,1,1,3],'500ml'=>[1,1,1,1,3],'700ml'=>[1,1,1,2,4]];
                $barLimits = ['PP'=>[1,1,1,1,3],'P'=>[2,1,1,2,4],'M'=>[2,1,1,2,4],'G'=>[3,1,1,3,5],'GG'=>[4,1,1,3,5],'XG'=>[5,1,1,4,6]];
                $limits = $isCup ? ($cupLimits[$row['nome']] ?? [1,1,1,1,1]) : ($barLimits[$row['nome']] ?? [1,1,1,1,1]);
                $row['volume_ml'] = $isCup ? (int)preg_replace('/[^0-9]/','',$row['nome']) : (['PP'=>500,'P'=>700,'M'=>1000,'G'=>1500,'GG'=>2000,'XG'=>3000][$row['nome']] ?? null);
                [$row['limite_frutas'],$row['limite_cremes'],$row['limite_adicionais'],$row['limite_caldas'],$row['limite_acompanhamentos']] = $limits;
                $sizes[] = $row;
            }
        } catch (Throwable $legacyError) {}
    }
}
if ($pdo && tableExists($pdo,'opcoes')) {
    try {
        $options = $pdo->query("SELECT id,tipo,nome,preco_adicional,imagem FROM opcoes WHERE ativo=1 ORDER BY tipo,ordem,id")->fetchAll();
    } catch (Throwable $e) {
        try {
            $options = $pdo->query("SELECT id,tipo,nome,preco_adicional FROM opcoes WHERE ativo=1 ORDER BY tipo,ordem,id")->fetchAll();
            foreach($options as &$option){ $option['imagem']=null; } unset($option);
        } catch (Throwable $legacyError) {}
    }
}

foreach ($sizes as &$sizeRow) {
    [$frutas, $cremes, $adicionais, $caldas, $acompanhamentos] = limitesPersonalizacao((string)$sizeRow['tipo'], (string)$sizeRow['nome']);
    $sizeRow['limite_frutas'] = $frutas;
    $sizeRow['limite_cremes'] = $cremes;
    $sizeRow['limite_adicionais'] = $adicionais;
    $sizeRow['limite_caldas'] = $caldas;
    $sizeRow['limite_acompanhamentos'] = $acompanhamentos;
}
unset($sizeRow);

if (!$sizes) {
    $sizes = [
        ['id'=>1,'tipo'=>'copo','nome'=>'180ml','preco'=>5,'volume_ml'=>180,'limite_frutas'=>1,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>1,'limite_acompanhamentos'=>1],
        ['id'=>2,'tipo'=>'copo','nome'=>'200ml','preco'=>7,'volume_ml'=>200,'limite_frutas'=>1,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>1,'limite_acompanhamentos'=>2],
        ['id'=>3,'tipo'=>'copo','nome'=>'300ml','preco'=>10,'volume_ml'=>300,'limite_frutas'=>1,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>1,'limite_acompanhamentos'=>2],
        ['id'=>4,'tipo'=>'copo','nome'=>'400ml','preco'=>13,'volume_ml'=>400,'limite_frutas'=>1,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>1,'limite_acompanhamentos'=>3],
        ['id'=>5,'tipo'=>'copo','nome'=>'500ml','preco'=>16,'volume_ml'=>500,'limite_frutas'=>1,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>1,'limite_acompanhamentos'=>3],
        ['id'=>6,'tipo'=>'copo','nome'=>'700ml','preco'=>20,'volume_ml'=>700,'limite_frutas'=>1,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>2,'limite_acompanhamentos'=>4],
        ['id'=>7,'tipo'=>'barca','nome'=>'PP','preco'=>15,'volume_ml'=>500,'limite_frutas'=>1,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>1,'limite_acompanhamentos'=>3],
        ['id'=>8,'tipo'=>'barca','nome'=>'P','preco'=>22,'volume_ml'=>700,'limite_frutas'=>2,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>2,'limite_acompanhamentos'=>4],
        ['id'=>9,'tipo'=>'barca','nome'=>'M','preco'=>30,'volume_ml'=>1000,'limite_frutas'=>2,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>2,'limite_acompanhamentos'=>4],
        ['id'=>10,'tipo'=>'barca','nome'=>'G','preco'=>40,'volume_ml'=>1500,'limite_frutas'=>3,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>3,'limite_acompanhamentos'=>5],
        ['id'=>11,'tipo'=>'barca','nome'=>'GG','preco'=>50,'volume_ml'=>2000,'limite_frutas'=>4,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>3,'limite_acompanhamentos'=>5],
        ['id'=>12,'tipo'=>'barca','nome'=>'XG','preco'=>70,'volume_ml'=>3000,'limite_frutas'=>5,'limite_cremes'=>1,'limite_adicionais'=>1,'limite_caldas'=>4,'limite_acompanhamentos'=>6],
    ];
}
if (!$options) {
    $groups = [
      'fruta'=>['Morango','Banana','Kiwi','Uva','Abacate','Abacaxi','Manga','Cereja','Mirtilo','Amora'],
      'creme'=>['Cupuaçu','Maracujá','Chocolate','Morango','Pistache'],
      'adicional'=>['Ovomaltine','Nutella','Kit-Kat','Kinder Ovo','Sonho de Valsa','Ferrero Rocher'],
      'calda'=>['Leite condensado','Chocolate','Morango','Caramelo'],
      'acompanhamento'=>['Leite em pó','Leite Ninho','Granola','Ovomaltine em pó','Paçoca','Amendoim','Castanha','Castanha-do-Pará','Farinha láctea','Neston','Sucrilhos','Flocos de arroz','Flocos de tapioca','Tapioca','Chia','Linhaça','Gergelim','Chocoball','Chocopower','Granulado de chocolate','Granulado colorido','Confete',"M&M's",'Gotas de chocolate','Jujuba','Marshmallow','Negresco/Oreo','Tubetes']
    ];
    $id=1;
    foreach($groups as $type=>$names){
        foreach($names as $name){
            $options[]=['id'=>$id++,'tipo'=>$type,'nome'=>$name,'preco_adicional'=>$type==='adicional'?2:0,'imagem'=>null];
        }
    }
}
$byType=[]; foreach($options as $opt) $byType[$opt['tipo']][]=$opt;
$labels=['fruta'=>'Frutas','creme'=>'Creme','adicional'=>'Adicional','calda'=>'Calda','acompanhamento'=>'Acompanhamentos'];
$groupIntro=[
    'fruta'=>'Escolha até o limite permitido para o tamanho selecionado.',
    'creme'=>'Escolha no máximo 1 creme. Esta opção é opcional.',
    'adicional'=>'Escolha no máximo 1 adicional. Cada adicional custa R$ 2,00.',
    'calda'=>'Escolha até o limite de caldas permitido para o tamanho selecionado.',
    'acompanhamento'=>'Escolha até o limite de acompanhamentos permitido para o tamanho selecionado.'
];
$rules=[];
foreach($sizes as $s){
    $key=(string)$s['id'];
    $rules[$key]=[
        'kind'=>$s['tipo'],
        'name'=>$s['nome'],
        'volume'=>$s['volume_ml'] ?? null,
        'fruta'=>(int)$s['limite_frutas'],
        'creme'=>(int)$s['limite_cremes'],
        'adicional'=>(int)$s['limite_adicionais'],
        'calda'=>(int)$s['limite_caldas'],
        'acompanhamento'=>(int)$s['limite_acompanhamentos']
    ];
}

$comboId=(int)($_GET['combo']??0);
$comboProduct=null;
$comboConfig=null;
$isComboBuilder=false;
if($comboId>0 && $pdo && tableExists($pdo,'produtos')){
    try{
        $stCombo=$pdo->prepare("SELECT p.*,c.nome categoria_nome FROM produtos p LEFT JOIN categorias c ON c.id=p.categoria_id WHERE p.id=? AND p.ativo=1 LIMIT 1");
        $stCombo->execute([$comboId]);
        $comboProduct=$stCombo->fetch() ?: null;
        if($comboProduct && mb_strtolower(trim((string)($comboProduct['categoria_nome']??'')))==='combos'){
            $comboConfig=getComboConfiguracao($pdo,$comboId,$comboProduct);
            $isComboBuilder=$comboConfig!==null;
        }
    }catch(Throwable $e){$comboProduct=null;$comboConfig=null;$isComboBuilder=false;}
}
$isComboCasal = $comboProduct && mb_strtolower(trim((string)$comboProduct['nome'])) === 'combo casal';
$comboDisplayBasePrice = $isComboCasal ? 0.0 : (float)($comboProduct['preco'] ?? 0);
$stepOffset = $specialOffers ? 1 : 0;
?>
<?php if($isComboBuilder): ?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">COMBO AÇAÍ FLOW</span>
    <h1>Monte seu <span><?=e($comboProduct['nome'])?>.</span></h1>
    <p>Você escolhe cada porção dentro das regras definidas no painel. Depois, personalize os complementos de cada uma.</p>
  </div>
</section>
<section class="section builder">
<div class="container">
<form id="customBuilder" class="builder-layout combo-builder-layout" data-mode="combo" data-combo-id="<?=e((string)$comboId)?>" data-combo-price="<?=e((string)$comboDisplayBasePrice)?>" data-combo-pricing="<?= $isComboCasal ? 'dynamic' : 'fixed' ?>" data-combo-config='<?=e(json_encode($comboConfig, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))?>' novalidate>
  <div class="builder-main">
    <div id="builderNotice" class="builder-notice" aria-live="polite"></div>
    <div class="builder-step builder-combo-intro">
      <div class="step-title"><span class="step-number">1</span><h3><?=e($comboProduct['nome'])?></h3></div>
      <p class="option-limit"><?=e($comboProduct['descricao']??'Escolha cada porção e personalize dentro das opções permitidas.')?></p>
      <div class="combo-price-note"><strong><?= $isComboCasal ? 'Preço calculado conforme suas escolhas' : 'Preço base: '.formatarMoeda((float)$comboDisplayBasePrice) ?></strong><span><?= $isComboCasal ? 'Cada copo ou barca soma o próprio preço ao total. Adicionais pagos entram à parte.' : 'Adicionais pagos, quando disponíveis, entram no total.' ?></span></div>
    </div>

    <?php foreach(($comboConfig['porcoes']??[]) as $slotIndex=>$slot):
        $allowedIds=array_map('intval',(array)($slot['tamanhos']??[]));
    ?>
      <section class="combo-slot-builder" data-combo-slot="<?=$slotIndex?>" data-limits='<?=e(json_encode($slot['limites']??[],JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))?>'>
        <div class="combo-slot-head">
          <div><span class="eyebrow">PORÇÃO <?=$slotIndex+1?></span><h3><?=e($slot['nome']??('Porção '.($slotIndex+1)))?></h3></div>
          <span class="combo-slot-status" data-slot-status>Escolha o formato e o tamanho</span>
        </div>
        <div class="combo-slot-step">
          <h4>Escolha o copo ou barca</h4>
          <p class="option-limit">Você só verá os tamanhos permitidos para esta porção.</p>
          <div class="option-grid builder-size-grid combo-size-grid">
            <?php foreach($sizes as $size): if(!in_array((int)$size['id'],$allowedIds,true) || !in_array($size['tipo'],(array)($slot['tipos']??[]),true)) continue; ?>
              <div class="option-card size-option-card">
                <input type="radio" name="combo_slot_<?=$slotIndex?>_size" id="combo-slot-<?=$slotIndex?>-size-<?=$size['id']?>" value="<?=$size['id']?>" data-kind="<?=e($size['tipo'])?>" data-label="<?=e($size['nome'])?>" data-volume="<?=e((string)($size['volume_ml']??''))?>" data-price="<?=e((string)$size['preco'])?>">
                <label for="combo-slot-<?=$slotIndex?>-size-<?=$size['id']?>"><span><strong><?=$size['tipo']==='barca'?'Barca ':''?><?=e($size['nome'])?></strong><small>R$ <?=number_format((float)$size['preco'],2,',','.')?></small></span></label>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="combo-slot-rules" data-slot-rules aria-live="polite"></div>
        <div class="combo-slot-options" data-slot-options>
          <?php foreach($labels as $type=>$label): ?>
            <div class="combo-option-group" data-slot-option-type="<?=e($type)?>">
              <div class="step-title combo-mini-title"><span class="step-number small">•</span><h4><?=$label?></h4></div>
              <p class="option-limit" data-slot-limit-text="<?=e($type)?>">Escolha o tamanho primeiro.</p>
              <div class="option-grid">
                <?php foreach(($byType[$type]??[]) as $opt): ?>
                  <div class="option-card">
                    <input type="checkbox" name="combo_slot_<?=$slotIndex?>_options[]" id="combo-opt-<?=$slotIndex?>-<?=$opt['id']?>" value="<?=$opt['id']?>" data-type="<?=e($opt['tipo'])?>" data-label="<?=e($opt['nome'])?>" data-price="<?=e($opt['preco_adicional'])?>" disabled>
                    <label for="combo-opt-<?=$slotIndex?>-<?=$opt['id']?>"><span><?=e($opt['nome'])?><?php if((float)$opt['preco_adicional']>0): ?><small>+ <?=formatarMoeda((float)$opt['preco_adicional'])?></small><?php endif; ?></span></label>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endforeach; ?>

    <div class="builder-step">
      <div class="step-title"><span class="step-number">2</span><h3>Observação</h3></div>
      <textarea class="form-control" name="observacao" placeholder="Ex.: pouco leite em pó, separar os adicionais, uma porção sem calda..."></textarea>
    </div>
    <div class="builder-step">
      <div class="step-title"><span class="step-number">3</span><h3>Quantidade</h3></div>
      <div class="quantity"><button type="button" data-qty-minus aria-label="Diminuir quantidade">−</button><input type="number" name="quantity" id="builderQuantity" value="1" min="1" max="20" aria-label="Quantidade"><button type="button" data-qty-plus aria-label="Aumentar quantidade">+</button></div>
    </div>
  </div>
  <aside class="builder-summary">
    <div class="summary-thumb"><img src="<?=produtoImagem($comboProduct['imagem']??null)?>" alt="<?=e($comboProduct['nome'])?>"></div>
    <h3>Seu combo</h3>
    <div id="builderSummary" class="summary-list"><strong>Escolha as porções</strong><span>Nenhuma porção escolhida</span></div>
    <div class="summary-total"><span>Total</span><span id="builderTotal">R$ 0,00</span></div>
    <button id="addCustomToCart" type="button" class="btn btn-bordo" disabled style="width:100%;margin-top:16px">ADICIONAR COMBO AO CARRINHO →</button>
    <a class="btn btn-outline" href="<?=url('cardapio.php')?>" style="width:100%;margin-top:10px">VOLTAR AO CARDÁPIO</a>
  </aside>
</form>
</div>
</section>
<?php else: ?>
<section class="page-hero">
  <div class="container">
    <span class="eyebrow">AÇAÍ FLOW</span>
    <h1>Monte do <span>seu jeito.</span></h1>
    <p>Escolha o tamanho, combine ingredientes e crie uma experiência com a sua cara.</p>
  </div>
</section>
<section class="section builder">
<div class="container">
<form id="customBuilder" class="builder-layout" data-mode="normal" data-rules='<?=e(json_encode($rules, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))?>' novalidate>
  <div class="builder-main">
    <div id="builderNotice" class="builder-notice" aria-live="polite"></div>
    <?php if ($specialOffers): ?>
    <div class="builder-step builder-special-step">
      <div class="step-title"><span class="step-number">1</span><h3>Combos e promoções</h3></div>
      <p class="option-limit">Veja as ofertas vigentes. Combos montáveis abrem sua composição; promoções simples continuam com adição direta.</p>
      <div class="builder-special-grid">
        <?php foreach ($specialOffers as $offer):
          $isCombo = mb_strtolower((string)($offer['categoria'] ?? '')) === 'combos';
          $label = $isCombo ? 'COMBO' : 'PROMOÇÃO';
          $offerImage = produtoImagem($offer['imagem'] ?? null);
        ?>
          <article class="product-card builder-special-card">
            <div class="product-thumb builder-special-thumb">
              <img src="<?=e($offerImage)?>" alt="<?=e($offer['nome'])?>" loading="lazy" decoding="async">
              <span class="builder-special-badge <?= $isCombo ? 'is-combo' : '' ?>"><?=e($label)?></span>
            </div>
            <span class="builder-special-type"><?=e($label)?></span>
            <h4><?=e($offer['nome'])?></h4>
            <?php if (!empty($offer['descricao'])): ?><p><?=e($offer['descricao'])?></p><?php endif; ?>
            <div class="builder-special-bottom">
              <strong><?=mb_strtolower(trim((string)$offer['nome'])) === 'combo casal' ? 'Conforme escolhas' : formatarMoeda((float)$offer['preco'])?></strong>
              <?php if($isCombo): ?>
                <a class="btn btn-bordo btn-small" href="<?=url('monte-seu-acai.php?combo='.(int)$offer['id'])?>">MONTAR →</a>
              <?php else: ?>
                <a class="btn btn-bordo btn-small" href="<?=url('produto.php?id='.(int)$offer['id'])?>">VER ITEM →</a>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="builder-step">
      <div class="step-title"><span class="step-number"><?=1 + $stepOffset?></span><h3>Escolha o tamanho</h3></div>
      <p class="option-limit">Selecione <strong>um tamanho</strong>. As quantidades permitidas de frutas, cremes, adicionais, caldas e acompanhamentos mudam conforme sua escolha.</p>
      <div class="option-grid builder-size-grid">
        <?php foreach(array_filter($sizes,fn($s)=>$s['tipo']==='copo') as $size): ?>
          <div class="option-card size-option-card">
            <input type="radio" name="size" id="size<?=$size['id']?>" value="<?=$size['id']?>" data-kind="copo" data-price="<?=e($size['preco'])?>" data-label="<?=e($size['nome'])?>">
            <label for="size<?=$size['id']?>"><span><strong><?=$size['nome']?></strong><small><?=formatarMoeda((float)$size['preco'])?></small></span></label>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="builder-step">
      <div class="step-title"><span class="step-number"><?=2 + $stepOffset?></span><h3>Ou escolha uma barca</h3></div>
      <p class="option-limit">Escolha uma barca. Os limites de personalização aparecem depois da seleção.</p>
      <div class="option-grid builder-size-grid">
        <?php foreach(array_filter($sizes,fn($s)=>$s['tipo']==='barca') as $size): ?>
          <div class="option-card size-option-card">
            <input type="radio" name="size" id="size<?=$size['id']?>" value="<?=$size['id']?>" data-kind="barca" data-price="<?=e($size['preco'])?>" data-label="<?=e($size['nome'])?>">
            <label for="size<?=$size['id']?>"><span><strong>Barca <?=$size['nome']?></strong><small><?=formatarMoeda((float)$size['preco'])?></small></span></label>
          </div>
        <?php endforeach; ?>
      </div>
      <div id="builderRules" class="builder-rules" aria-live="polite"></div>
    </div>
    <?php $step=3 + $stepOffset; foreach($labels as $type=>$label): ?>
      <div class="builder-step option-step" data-option-type="<?=e($type)?>">
        <div class="step-title"><span class="step-number"><?=$step++?></span><h3><?=$label?></h3></div>
        <p class="option-limit" data-limit-text="<?=$type?>"><?=e($groupIntro[$type])?></p>
        <div class="option-grid">
        <?php foreach(($byType[$type]??[]) as $opt): ?>
          <div class="option-card">
            <input type="checkbox" name="options[]" id="opt<?=$opt['id']?>" value="<?=$opt['id']?>" data-type="<?=e($opt['tipo'])?>" data-label="<?=e($opt['nome'])?>" data-price="<?=e($opt['preco_adicional'])?>">
            <label for="opt<?=$opt['id']?>"><span><?=e($opt['nome'])?><?php if((float)$opt['preco_adicional']>0): ?><small>+ <?=formatarMoeda((float)$opt['preco_adicional'])?></small><?php endif; ?></span></label>
          </div>
        <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
    <div class="builder-step"><div class="step-title"><span class="step-number"><?=8 + $stepOffset?></span><h3>Observação</h3></div><textarea class="form-control" name="observacao" placeholder="Ex.: pouco leite em pó, separar os adicionais, tocar campainha..."></textarea></div>
    <div class="builder-step"><div class="step-title"><span class="step-number"><?=9 + $stepOffset?></span><h3>Quantidade</h3></div><div class="quantity"><button type="button" data-qty-minus aria-label="Diminuir quantidade">−</button><input type="number" name="quantity" id="builderQuantity" value="1" min="1" max="20" aria-label="Quantidade"><button type="button" data-qty-plus aria-label="Aumentar quantidade">+</button></div></div>
  </div>
  <aside class="builder-summary">
    <div class="summary-thumb"><img src="<?=asset('img/banners/hero-produto-novo.png')?>" alt="Açaí Flow personalizado"></div>
    <h3>Seu açaí</h3><div id="builderSummary" class="summary-list"><strong>Escolha um tamanho</strong><span>Nenhuma opção escolhida</span></div>
    <div class="summary-total"><span>Total</span><span id="builderTotal">R$ 0,00</span></div>
    <button id="addCustomToCart" type="button" class="btn btn-bordo" disabled style="width:100%;margin-top:16px">ADICIONAR AO CARRINHO →</button>
    <p class="form-help" style="margin-top:10px">O pagamento será realizado no ato da entrega.</p>
  </aside>
</form>
</div>
</section>
<?php endif; ?>
<?php $extraJs=['js/personalizacao.js']; require __DIR__.'/includes/footer.php'; ?>
