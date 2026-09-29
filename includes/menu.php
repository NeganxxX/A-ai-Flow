<?php $cartCount = 0; ?>
<header class="site-header">
    <div class="container header-shell">
        <a class="brand" href="<?=url('index.php')?>">
            <img src="<?=asset('img/logo/logo-real.png')?>" alt="Açaí Flow">
        </a>
        <button class="menu-toggle" type="button" aria-label="Abrir menu" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <nav class="main-nav" aria-label="Navegação principal">
            <a class="<?=$activePage === 'inicio' ? 'active' : ''?>" href="<?=url('index.php')?>">Início</a>
            <a class="<?=$activePage === 'cardapio' ? 'active' : ''?>" href="<?=url('cardapio.php')?>">Cardápio</a>
            <a class="<?=$activePage === 'monte' ? 'active' : ''?>" href="<?=url('monte-seu-acai.php')?>">Monte seu açaí</a>
            <a class="<?=$activePage === 'sobre' ? 'active' : ''?>" href="<?=url('sobre.php')?>">Sobre</a>
            <a class="<?=$activePage === 'contato' ? 'active' : ''?>" href="<?=url('contato.php')?>">Contato</a>
            <div class="mobile-header-hours">
                <span>ATENDIMENTO</span>
                <strong><?=e(SITE_HORARIO_SEMANA)?></strong>
                <strong><?=e(SITE_HORARIO_SABADO)?></strong>
            </div>
        </nav>
        <div class="header-hours" aria-label="Horário de funcionamento">
            <span class="header-hours-label">ATENDIMENTO</span>
            <strong><?=e(SITE_HORARIO_SEMANA)?></strong>
            <span class="header-hours-sep">•</span>
            <strong><?=e(SITE_HORARIO_SABADO)?></strong>
        </div>
        <div class="header-actions">
            <a class="btn btn-coral btn-small desktop-only" href="<?=url('cardapio.php')?>">PEDIR AGORA <span>→</span></a>
            <button class="theme-toggle" type="button" aria-label="Ativar modo noturno" aria-pressed="false" title="Ativar modo noturno">
                <svg class="theme-icon theme-moon" viewBox="0 0 24 24" aria-hidden="true"><path d="M20.8 14.2A8.8 8.8 0 0 1 9.8 3.2a8.9 8.9 0 1 0 11 11Z"/></svg>
                <svg class="theme-icon theme-sun" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
            </button>
            <a class="header-icon" href="<?=url(isClienteLogado() ? 'cliente/index.php' : 'login.php')?>" aria-label="Área do cliente">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="8" r="3.2"></circle><path d="M5 20c.8-3.2 3.2-5 7-5s6.2 1.8 7 5"></path></svg>
            </a>
            <a class="cart-link" href="<?=url('pedido.php')?>" aria-label="Carrinho">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h2l1.3 9.1a2 2 0 0 0 2 1.7h6.9a2 2 0 0 0 1.9-1.5L19.7 8H7"></path><circle cx="10" cy="19" r="1.2"></circle><circle cx="17" cy="19" r="1.2"></circle></svg>
                <span class="cart-count" id="cartCount">0</span>
            </a>
        </div>
    </div>
</header>
