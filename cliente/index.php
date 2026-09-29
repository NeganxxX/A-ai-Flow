<?php
declare(strict_types=1);
require_once __DIR__.'/../config/seguranca.php';
exigirLogin();
$pageTitle='Área do cliente';
$extraCss=['css/cliente.css'];$bodyClass='has-client-tabbar';
require __DIR__.'/../includes/header.php';

$pdo=getPDO();$orders=[];$errorMessage='';
if(!$pdo){
    $errorMessage='Não foi possível conectar ao banco de dados.';
}else{
    try{
        $st=$pdo->prepare('SELECT id,total,status,criado_em FROM pedidos WHERE usuario_id=? ORDER BY id DESC LIMIT 6');
        $st->execute([(int)$_SESSION['cliente_id']]);
        $orders=$st->fetchAll();
    }catch(Throwable $e){
        error_log('[Açai Flow] Área do cliente: '.$e->getMessage());
        $errorMessage='Não foi possível carregar seus pedidos agora.';
    }
}
$last=$orders[0]??null;
$inProgress=$last && !in_array((string)$last['status'],['entregue','cancelado'],true);
$name=(string)($_SESSION['cliente_nome']??'Cliente');
$initials='';
foreach(preg_split('/\s+/',trim($name)) as $part){ if($part!==''){ $initials.=mb_strtoupper(mb_substr($part,0,1)); if(mb_strlen($initials)>=2) break; } }
$initials=$initials?:'A';
$steps=['recebido','confirmado','preparando','pronto','saiu_entrega','entregue'];
$currentStep=$last ? array_search((string)$last['status'],$steps,true) : false;
if($currentStep===false)$currentStep=0;
?>
<section class="section page-shell client-page client-area">
<div class="container client-shell">
    <?php $clientActive='inicio';require __DIR__.'/../includes/cliente-nav.php'; ?>

    <div class="client-content">
        <div class="client-hero">
            <div class="client-hero-top">
                <div>
                    <span class="eyebrow">Açaí Flow • Minha conta</span>
                    <h1>Olá, <?=e($name)?>!</h1>
                    <p>Acompanhe seus pedidos, veja seu histórico e volte rapidamente para o seu próximo sabor.</p>
                </div>
                <div class="client-hero-actions">
                    <a class="btn btn-coral" href="<?=url('monte-seu-acai.php')?>">MONTE SEU AÇAÍ →</a>
                    <a class="btn btn-outline" href="<?=url('cliente/meus-pedidos.php')?>">VER PEDIDOS</a>
                </div>
            </div>
        </div>

        <div class="client-stats">
            <article class="client-stat"><div class="client-stat-top"><span class="client-stat-label">Pedidos recentes</span><span class="client-stat-icon">▣</span></div><div class="client-stat-value"><?=count($orders)?></div><div class="client-stat-note">Mostrados nesta área</div></article>
            <article class="client-stat"><div class="client-stat-top"><span class="client-stat-label">Último pedido</span><span class="client-stat-icon">◷</span></div><div class="client-stat-value"><?= $last ? '#'.e($last['id']) : '—' ?></div><div class="client-stat-note"><?= $last ? formatarData($last['criado_em']) : 'Ainda não há pedidos' ?></div></article>
            <article class="client-stat"><div class="client-stat-top"><span class="client-stat-label">Status</span><span class="client-stat-icon">✓</span></div><div class="client-stat-value is-text"><?= $last ? e(statusLabel($last['status'])) : 'Pronto' ?></div><div class="client-stat-note"><?= $inProgress ? 'Pedido em andamento' : 'Sem pedido em andamento' ?></div></article>
            <article class="client-stat"><div class="client-stat-top"><span class="client-stat-label">Acesso rápido</span><span class="client-stat-icon">✦</span></div><div class="client-stat-value is-text">Seu próximo sabor</div><div class="client-stat-note"><a class="client-stat-link" href="<?=url('monte-seu-acai.php')?>">Montar agora →</a></div></article>
        </div>

        <div class="client-grid">
            <div>
                <section class="client-panel">
                    <div class="client-panel-head">
                        <div><h2><?= $inProgress ? 'Seu pedido está em andamento' : 'Seu último pedido' ?></h2><p><?= $last ? 'Acompanhe o andamento sem sair da sua área.' : 'Quando você fizer um pedido, ele aparecerá aqui.' ?></p></div>
                        <?php if($last): ?><a class="client-panel-link" href="<?=url('cliente/pedido-detalhes.php?id='.$last['id'])?>">VER DETALHES →</a><?php endif; ?>
                    </div>
                    <?php if($errorMessage): ?>
                        <div class="client-empty"><div class="client-empty-icon">!</div><h3>Não foi possível carregar seus pedidos</h3><p><?=e($errorMessage)?></p></div>
                    <?php elseif(!$last): ?>
                        <div class="client-empty"><div class="client-empty-icon">🍇</div><h3>Seu histórico começa aqui</h3><p>Monte seu açaí, escolha seus complementos e acompanhe tudo por esta área.</p><a class="btn btn-bordo" href="<?=url('monte-seu-acai.php')?>">MONTAR MEU AÇAÍ →</a></div>
                    <?php else: ?>
                        <div class="client-order-highlight">
                            <div>
                                <div class="client-order-id"><strong>Pedido #<?=e($last['id'])?></strong><span class="client-order-date"><?=formatarData($last['criado_em'])?></span></div>
                                <div class="client-order-total"><?=formatarMoeda((float)$last['total'])?></div>
                                <div class="client-order-status"><span class="status-pill <?=statusClasse($last['status'])?>"><?=e(statusLabel($last['status']))?></span></div>
                                <div class="client-mini-tracker">
                                    <?php foreach(array_slice($steps,0,4) as $idx=>$step): ?><div class="client-track-step <?=$idx<$currentStep?'done':''?> <?=$idx===$currentStep?'current':''?>"><div class="client-track-dot">✓</div><div class="client-track-label"><?=e(statusLabel($step))?></div></div><?php endforeach; ?>
                                </div>
                            </div>
                            <div class="client-order-action">
                                <a class="btn btn-bordo" href="<?=url('cliente/pedido-detalhes.php?id='.$last['id'])?>">ACOMPANHAR</a>
                                <a class="btn btn-outline" href="<?=url('cliente/meus-pedidos.php')?>">HISTÓRICO</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>

                <section class="client-panel">
                    <div class="client-panel-head"><div><h2>Pedidos recentes</h2><p>Um acesso rápido ao seu histórico.</p></div><a class="client-panel-link" href="<?=url('cliente/meus-pedidos.php')?>">VER TODOS →</a></div>
                    <?php if($orders): ?>
                    <div class="client-order-list">
                        <?php foreach($orders as $o): ?>
                        <div class="client-order-row">
                            <div class="client-order-badge">#<?=e($o['id'])?></div>
                            <div class="client-order-main"><strong><?=e(statusLabel($o['status']))?></strong><span><?=formatarData($o['criado_em'])?></span></div>
                            <span class="status-pill <?=statusClasse($o['status'])?>"><?=e(statusLabel($o['status']))?></span>
                            <div class="client-order-price"><?=formatarMoeda((float)$o['total'])?></div>
                            <a class="mini-btn" href="<?=url('cliente/pedido-detalhes.php?id='.$o['id'])?>">Detalhes</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?><div class="client-empty"><div class="client-empty-icon">▣</div><h3>Nenhum pedido ainda</h3><p>Quando fizer seu primeiro pedido, ele será listado aqui.</p></div><?php endif; ?>
                </section>
            </div>

            <aside>
                <section class="client-panel">
                    <div class="client-panel-head"><div><h2>Acessos rápidos</h2><p>O essencial em poucos cliques.</p></div></div>
                    <div class="client-shortcuts">
                        <a class="client-shortcut" href="<?=url('monte-seu-acai.php')?>"><span class="client-shortcut-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 4c1.4 1.3 2.1 2.8 2.1 4.5S9.4 12 8 13.3c-1.4-1.3-2.1-2.8-2.1-4.8S6.6 5.3 8 4Z"/><path d="M16 4c1.4 1.3 2.1 2.8 2.1 4.5S17.4 12 16 13.3c-1.4-1.3-2.1-2.8-2.1-4.8S14.6 5.3 16 4Z"/><path d="M8.4 13.4c.7 2.7 2.1 4.8 3.6 6.6 1.5-1.8 2.9-3.9 3.6-6.6"/></svg></span><span><strong>Montar meu açaí</strong><span>Escolha tamanho e complementos.</span></span></a>
                        <a class="client-shortcut" href="<?=url('cardapio.php')?>"><span class="client-shortcut-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="5" y="4" width="14" height="16" rx="2"/><path d="M8 8h8M8 12h8M8 16h5"/></svg></span><span><strong>Ver cardápio</strong><span>Confira todos os tamanhos disponíveis.</span></span></a>
                        <a class="client-shortcut" href="<?=url('cliente/meus-dados.php')?>"><span class="client-shortcut-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.5"/><path d="M5.5 19c.9-3.2 3.1-4.8 6.5-4.8s5.6 1.6 6.5 4.8"/></svg></span><span><strong>Meus dados</strong><span>Atualize suas informações.</span></span></a>
                        <a class="client-shortcut" href="<?=url('contato.php')?>"><span class="client-shortcut-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="4" y="6" width="16" height="12" rx="2"/><path d="m5.5 8 6.5 5 6.5-5"/></svg></span><span><strong>Fale com a gente</strong><span>Tire uma dúvida com a Açaí Flow.</span></span></a>
                    </div>
                </section>
                <div class="client-tip"><strong>Seu pedido, do seu jeito.</strong><p>A área do cliente foi pensada para você encontrar o que precisa sem perder tempo.</p><a class="btn btn-bordo" href="<?=url('monte-seu-acai.php')?>">ESCOLHER MEU AÇAÍ →</a></div>
            </aside>
        </div>
    </div>
</div>
</section>
<?php require __DIR__.'/../includes/footer.php'; ?>
