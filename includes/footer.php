</main>
<footer class="site-footer">
    <div class="footer-wave"></div>
    <div class="container footer-grid">
        <div class="footer-brand-block">
            <img src="<?=asset('img/logo/logo-real.png')?>" alt="Açaí Flow" class="footer-logo">
            <p>Açaí expresso com sabor, rapidez, qualidade e liberdade para montar do seu jeito.</p>
        </div>
        <div>
            <h3>Explorar</h3>
            <a href="<?=url('index.php')?>">Início</a>
            <a href="<?=url('cardapio.php')?>">Cardápio</a>
            <a href="<?=url('monte-seu-acai.php')?>">Monte seu açaí</a>
            <a href="<?=url('sobre.php')?>">Sobre</a>
        </div>
        <div>
            <h3>Atendimento</h3>
            <a href="<?=url('contato.php')?>">Fale com a gente</a>
            <a href="<?=url('login.php')?>">Área do cliente</a>
            <a href="<?=url('acompanhamento.php')?>">Acompanhar pedido</a>
        </div>
        <div class="footer-flow">Mais que açaí,<strong>é o seu momento!</strong></div>
    </div>
    <div class="container footer-bottom">
        <span>© <?=date('Y')?> Açaí Flow</span>
        <span>Manacapuru — AM</span>
    </div>
</footer>
<script>window.ACAI_BASE_URL = <?=json_encode(BASE_URL, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)?>; window.ACAI_CART_SYNC = <?=json_encode(['authenticated'=>isClienteLogado(),'csrf'=>csrfToken(),'endpoint'=>url('processa/carrinho.php'),'serverCart'=>$_SESSION['cart'] ?? []], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)?>;</script>
<script src="<?=asset('js/script.js')?>"></script>
<script src="<?=asset('js/carrinho.js')?>?v=<?=filemtime(__DIR__.'/../js/carrinho.js')?>"></script>
<script src="<?=asset('js/tema.js')?>?v=<?=filemtime(__DIR__.'/../js/tema.js')?>"></script>
<?php foreach (($extraJs ?? []) as $js): ?><script src="<?=asset($js)?>?v=<?=filemtime(__DIR__.'/../'.$js)?>"></script><?php endforeach; ?>
</body>
</html>
