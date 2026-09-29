<?php
/**
 * Navegação da Área do cliente.
 *
 * Renderiza três peças a partir de uma única fonte (antes o menu era copiado em cada página):
 *  - .client-sidebar    → menu lateral (computador, acima de 1024px)
 *  - .client-mobile-bar → cartão da conta (tablet e celular, até 1024px)
 *  - .client-tabs       → abas: segmentadas no tablet, barra fixa inferior no celular
 *
 * Uso: defina $clientActive ('inicio' | 'pedidos' | 'dados') e, opcionalmente, $clientName
 * antes de dar require neste arquivo, dentro de .client-shell.
 */
$clientActive = $clientActive ?? 'inicio';
$cNome = trim((string)($clientName ?? ($_SESSION['cliente_nome'] ?? 'Cliente')));
if ($cNome === '') { $cNome = 'Cliente'; }
$cPartes = preg_split('/\s+/', $cNome) ?: [$cNome];
$cIni = '';
foreach ($cPartes as $cParte) {
    if ($cParte !== '') {
        $cIni .= mb_strtoupper(mb_substr($cParte, 0, 1));
        if (mb_strlen($cIni) >= 2) { break; }
    }
}
$cIni = $cIni ?: 'A';

$cIcones = [
    'inicio'  => '<path d="M4 11.5 12 5l8 6.5"/><path d="M6.5 10.2V19h11v-8.8"/><path d="M10 19v-5h4v5"/>',
    'pedidos' => '<rect x="5" y="4" width="14" height="16" rx="2"/><path d="M8.5 9h7M8.5 12.5h7M8.5 16h4"/>',
    'dados'   => '<circle cx="12" cy="8" r="3.5"/><path d="M5.5 19c.9-3.2 3.1-4.8 6.5-4.8s5.6 1.6 6.5 4.8"/>',
    'montar'  => '<path d="M6 8.5h12l-1.3 9.4a2 2 0 0 1-2 1.7H9.3a2 2 0 0 1-2-1.7L6 8.5Z"/><path d="M5 8.5h14"/><path d="M12 8.5V4.2l3-1.2"/>',
];
$cAbas = [
    'inicio'  => ['cliente/index.php', 'Minha área', 'Início'],
    'pedidos' => ['cliente/meus-pedidos.php', 'Meus pedidos', 'Pedidos'],
    'dados'   => ['cliente/meus-dados.php', 'Meus dados', 'Meus dados'],
    'montar'  => ['monte-seu-acai.php', 'Montar açaí', 'Montar'],
];
$cSvg = static fn(string $k): string => '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . $cIcones[$k] . '</svg>';
?>
<aside class="client-sidebar">
    <div class="client-sidebar-brand">
        <img src="<?=asset('img/logo/logo-real.png')?>" alt="Açaí Flow">
        <div><strong>Área do cliente</strong><span>Seu sabor. Seu flow.</span></div>
    </div>
    <div class="client-profile-mini">
        <div class="client-avatar"><?=e($cIni)?></div>
        <div><strong><?=e($cNome)?></strong><span>Cliente Açaí Flow</span></div>
    </div>
    <div class="client-nav-title">Navegação</div>
    <nav class="client-nav" aria-label="Área do cliente">
        <a class="<?=$clientActive === 'inicio' ? 'active' : ''?>" href="<?=url('cliente/index.php')?>"<?=$clientActive === 'inicio' ? ' aria-current="page"' : ''?>><span class="client-nav-icon">⌂</span>Minha área</a>
        <a class="<?=$clientActive === 'pedidos' ? 'active' : ''?>" href="<?=url('cliente/meus-pedidos.php')?>"<?=$clientActive === 'pedidos' ? ' aria-current="page"' : ''?>><span class="client-nav-icon">▣</span>Meus pedidos</a>
        <a class="<?=$clientActive === 'dados' ? 'active' : ''?>" href="<?=url('cliente/meus-dados.php')?>"<?=$clientActive === 'dados' ? ' aria-current="page"' : ''?>><span class="client-nav-icon">◉</span>Meus dados</a>
        <a href="<?=url('monte-seu-acai.php')?>"><span class="client-nav-icon">✦</span>Montar açaí</a>
        <div class="client-nav-separator"></div>
        <a class="client-logout" href="<?=url('logout.php')?>"><span class="client-nav-icon">↪</span>Sair da conta</a>
    </nav>
    <div class="client-sidebar-cta">
        <strong>Bateu vontade?</strong>
        <p>Monte seu próximo açaí em poucos passos.</p>
        <a class="btn btn-coral" href="<?=url('monte-seu-acai.php')?>">MONTAR AGORA →</a>
    </div>
</aside>

<div class="client-mobile-bar">
    <div class="client-avatar" aria-hidden="true"><?=e($cIni)?></div>
    <div class="client-mobile-id">
        <strong><?=e($cNome)?></strong>
        <span>Cliente Açaí Flow</span>
    </div>
    <a class="client-mobile-logout" href="<?=url('logout.php')?>" aria-label="Sair da conta">
        <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M9 5H6.5A1.5 1.5 0 0 0 5 6.5v11A1.5 1.5 0 0 0 6.5 19H9"/><path d="m14 8 4 4-4 4M18 12H9"/></svg>
        <span>Sair</span>
    </a>
</div>

<nav class="client-tabs" aria-label="Menu da área do cliente">
    <?php foreach ($cAbas as $cChave => [$cRota, $cRotuloLongo, $cRotuloCurto]): $cAtiva = ($clientActive === $cChave); ?>
    <a class="client-tab<?=$cAtiva ? ' is-active' : ''?>" href="<?=url($cRota)?>"<?=$cAtiva ? ' aria-current="page"' : ''?> aria-label="<?=e($cRotuloLongo)?>">
        <?=$cSvg($cChave)?>
        <span class="client-tab-label"><span class="tab-long"><?=e($cRotuloLongo)?></span><span class="tab-short"><?=e($cRotuloCurto)?></span></span>
    </a>
    <?php endforeach; ?>
</nav>
