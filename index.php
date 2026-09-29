<?php
$pageTitle = 'Início';
$activePage = 'inicio';
require __DIR__ . '/includes/header.php';
?>

<?php if (!isClienteLogado()): ?>
<div class="client-welcome" id="clientWelcome" aria-hidden="true">
    <div class="client-welcome-backdrop" data-welcome-close></div>
    <section class="client-welcome-card" role="dialog" aria-modal="true" aria-labelledby="clientWelcomeTitle">
        <button class="client-welcome-close" type="button" aria-label="Fechar" data-welcome-close>×</button>
        <span class="client-welcome-eyebrow">AÇAÍ FLOW</span>
        <h2 id="clientWelcomeTitle">Você já é cliente da nossa marca?</h2>
        <p>Entre na sua conta para acompanhar pedidos e deixar seu próximo açaí ainda mais rápido.</p>
        <div class="client-welcome-actions">
            <a class="btn btn-coral" href="<?=url('login.php')?>">SIM, ENTRAR <span>→</span></a>
            <button class="btn btn-outline client-welcome-later" type="button" data-welcome-close>DEIXAR PARA DEPOIS</button>
        </div>
    </section>
</div>
<?php endif; ?>

<section class="hero flow-rotating-banner" aria-labelledby="hero-title">
    <div class="container hero-grid">
        <div class="hero-copy">
            <div class="hero-location">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s7-6.1 7-12A7 7 0 0 0 5 9c0 5.9 7 12 7 12Z"/><circle cx="12" cy="9" r="2.2"/></svg>
                <span>MANACAPURU - AM</span>
            </div>

            <h1 id="hero-title">Seu sabor<br><span>Seu ritmo.</span></h1>

            <p>
                Monte sua combinação favorita e viva uma experiência única,
                com todo o sabor da Amazônia.
            </p>

            <div class="hero-buttons">
                <a class="btn btn-coral" href="<?=url('monte-seu-acai.php')?>">MONTE SEU AÇAÍ <span>→</span></a>
                <a class="btn btn-outline" href="<?=url('cardapio.php')?>">VER CARDÁPIO</a>
            </div>
        </div>

        <div class="hero-art" aria-label="Produto em destaque da Açaí Flow">
            <div class="hero-fruits" aria-hidden="true">
                <span class="hero-fruit hero-fruit-1"><img src="<?=asset('img/animacoes/morango.svg')?>" alt=""></span>
                <span class="hero-fruit hero-fruit-2"><img src="<?=asset('img/animacoes/banana.svg')?>" alt=""></span>
                <span class="hero-fruit hero-fruit-3"><img src="<?=asset('img/animacoes/morango.svg')?>" alt=""></span>
                <span class="hero-fruit hero-fruit-4"><img src="<?=asset('img/animacoes/banana.svg')?>" alt=""></span>
                <span class="hero-fruit hero-fruit-5"><img src="<?=asset('img/animacoes/morango.svg')?>" alt=""></span>
            </div>
            <div class="hero-product-wrap">
                <img class="hero-product-img" src="<?=asset('img/banners/hero-produto-novo.png')?>" alt="Copo de açaí Açaí Flow com frutas e acompanhamentos">
            </div>
        </div>
    </div>
</section>



<section class="benefits reveal" data-reveal aria-label="Benefícios da Açaí Flow">
    <div class="container benefits-grid">
        <article class="benefit reveal reveal-delay-1" data-reveal>
            <div class="benefit-icon" aria-hidden="true">
                <svg viewBox="0 0 48 48"><path d="M8 23h32l-3 15H11L8 23Z"/><path d="M9 23c2-7 7-10 15-10s13 3 15 10"/><path d="M17 9c4 0 7 2 8 6-5 1-9-1-10-6h2Z"/></svg>
            </div>
            <h3>Sabor</h3>
            <p>Frutas e ingredientes selecionados</p>
        </article>

        <article class="benefit reveal reveal-delay-1" data-reveal>
            <div class="benefit-icon" aria-hidden="true">
                <svg viewBox="0 0 48 48"><path d="M27 5 14 26h10l-3 17 13-23H24l3-15Z"/></svg>
            </div>
            <h3>Rapidez</h3>
            <p>Seu açaí do seu jeito, sem demora</p>
        </article>

        <article class="benefit reveal reveal-delay-1" data-reveal>
            <div class="benefit-icon" aria-hidden="true">
                <svg viewBox="0 0 48 48"><path d="M16 37c10-11 10-21 11-28"/><path d="M27 14c5-3 10-1 14 2-4 5-9 7-14 4"/><path d="M17 28c-5-1-8-4-9-9 6-1 10 2 11 7"/></svg>
            </div>
            <h3>Qualidade</h3>
            <p>Você sente a diferença em cada colherada</p>
        </article>

        <article class="benefit reveal reveal-delay-1" data-reveal>
            <div class="benefit-icon" aria-hidden="true">
                <svg viewBox="0 0 48 48"><path d="M24 39S10 30.5 10 19a7 7 0 0 1 14-1 7 7 0 0 1 14 1c0 11.5-14 20-14 20Z"/></svg>
            </div>
            <h3>Personalização</h3>
            <p>Monte do seu jeito, combinações infinitas</p>
        </article>
    </div>
</section>

<section class="section menu-preview reveal" data-reveal aria-labelledby="menu-preview-title">
    <div class="section-decor-leaf left" aria-hidden="true"></div>
    <div class="section-decor-leaf right" aria-hidden="true"></div>

    <div class="container menu-preview-grid">
        <div class="menu-intro">
            <span class="eyebrow">AÇAÍ EXPRESSO</span>
            <h2 id="menu-preview-title" class="display-script">Nosso <span>Cardápio.</span></h2>
            <p>Açaí em diferentes tamanhos e formatos, sempre com muito sabor e qualidade.</p>
            <a class="btn btn-coral" href="<?=url('cardapio.php')?>">VER CARDÁPIO <span>→</span></a>
        </div>

        <div class="preview-cards">
            <article class="product-card category-card reveal reveal-delay-2" data-reveal>
                <div class="product-thumb category-thumb">
                    <img src="<?=asset('img/categorias/copos.svg')?>" alt="Copos de açaí" loading="lazy">
                </div>
                <h3>Copos</h3>
                <p>A partir de R$ 5,00</p>
                <div class="card-actions">
                    <a class="btn btn-bordo" href="<?=url('cardapio.php')?>">VER OPÇÕES <span>→</span></a>
                </div>
            </article>

            <article class="product-card category-card reveal reveal-delay-2" data-reveal>
                <div class="product-thumb category-thumb">
                    <img src="<?=asset('img/categorias/barcas.svg')?>" alt="Barcas de açaí" loading="lazy">
                </div>
                <h3>Barcas</h3>
                <p>A partir de R$ 15,00</p>
                <div class="card-actions">
                    <a class="btn btn-bordo" href="<?=url('cardapio.php')?>">VER OPÇÕES <span>→</span></a>
                </div>
            </article>

        </div>
    </div>
</section>

<section class="section customize-strip reveal" data-reveal aria-labelledby="customize-title">
    <div class="container customize-layout">
        <div class="customize-art">
            <img src="<?=asset('img/customizador.svg')?>" alt="Açaí personalizado">
        </div>

        <div class="customize-content">
            <span class="eyebrow">MONTE DO SEU JEITO</span>
            <h2 id="customize-title" class="display-script">Monte do <span>seu jeito.</span></h2>
            <p>Escolha seus ingredientes favoritos e crie a combinação perfeita para você.</p>

            <div class="option-groups">
                <div class="option-group">
                    <h4>Frutas</h4>
                    <ul>
                        <li>Morango</li>
                        <li>Banana</li>
                        <li>Kiwi</li>
                        <li>Uva</li>
                    </ul>
                </div>
                <div class="option-group">
                    <h4>Cremes</h4>
                    <ul>
                        <li>Cupuaçu</li>
                        <li>Maracujá</li>
                        <li>Chocolate</li>
                        <li>Pistache</li>
                    </ul>
                </div>
                <div class="option-group">
                    <h4>Caldas</h4>
                    <ul>
                        <li>Leite condensado</li>
                        <li>Chocolate</li>
                        <li>Morango</li>
                        <li>Caramelo</li>
                    </ul>
                </div>
                <div class="option-group">
                    <h4>Acompanhamentos</h4>
                    <ul>
                        <li>Granola</li>
                        <li>Leite Ninho</li>
                        <li>Ovomaltine</li>
                        <li>Castanhas</li>
                    </ul>
                </div>
            </div>

            <a class="btn btn-bordo" href="<?=url('monte-seu-acai.php')?>">MONTE AGORA <span>→</span></a>
        </div>
    </div>
</section>

<section class="section about-home reveal" data-reveal aria-labelledby="about-title">
    <div class="container about-home-inner">
        <div class="about-home-photo">
            <img src="<?=asset('img/institucional/manacapuru.jpg')?>" alt="Vista de Manacapuru, no Amazonas" loading="lazy">
        </div>
        <div class="about-copy">
            <span class="eyebrow">SOBRE A AÇAÍ FLOW</span>
            <h2 id="about-title" class="display-script">Sabores da <span>Amazônia.</span></h2>
            <p>
                Criada em Manacapuru-AM, a Açaí Flow é inspirada pela riqueza natural e pela cultura da região.
                Valorizamos o açaí amazônico em uma experiência moderna, saborosa, rápida e personalizada.
            </p>
            <a class="btn btn-coral" href="<?=url('sobre.php')?>">CONHEÇA MAIS <span>→</span></a>
        </div>
        <div class="about-callout">Sabor da Amazônia,<br><span>perto de você!</span></div>
    </div>
</section>

<section class="section contact-strip reveal" data-reveal aria-label="Canais de atendimento">
    <div class="container contact-bar">
        <div class="contact-intro">
            <h2 class="display-script">Fale com a gente</h2>
            <p>Estamos sempre por aqui, prontos para te atender!</p>
        </div>

        <a href="<?=url('contato.php')?>" class="contact-item">
            <div class="contact-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M20 11.5a8.5 8.5 0 0 1-12.9 7.3L4 20l1.2-3.1A8.5 8.5 0 1 1 20 11.5Z"/><path d="M8.5 9.5c.4 2.3 2 4 4.4 4.4l1.2-1.2c.2-.2.5-.3.8-.2l1.7.6c.3.1.5.4.5.7v1.2c0 .4-.3.7-.7.7A8.9 8.9 0 0 1 8.3 7.1c0-.4.3-.7.7-.7h1.2c.3 0 .6.2.7.5l.6 1.7c.1.3 0 .6-.2.8L10 10.6Z"/></svg>
            </div>
            <div><strong>WhatsApp</strong><span>Fale com a gente</span></div>
        </a>

        <a href="<?=url('contato.php')?>" class="contact-item">
            <div class="contact-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><rect x="4" y="4" width="16" height="16" rx="5"/><circle cx="12" cy="12" r="3.5"/><circle cx="17.3" cy="6.8" r="1" fill="currentColor" stroke="none"/></svg>
            </div>
            <div><strong>Instagram</strong><span>@acaiflow</span></div>
        </a>

        <a href="<?=url('contato.php')?>" class="contact-item">
            <div class="contact-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24"><path d="M12 21s7-6.1 7-12A7 7 0 0 0 5 9c0 5.9 7 12 7 12Z"/><circle cx="12" cy="9" r="2.2"/></svg>
            </div>
            <div><strong>Manacapuru-AM</strong><span>Nossa localização</span></div>
        </a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
