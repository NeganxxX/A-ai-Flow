# Açaí Flow — Projeto integrado

Projeto preparado para XAMPP, usando PHP, MySQL/SQL, HTML, CSS e JavaScript.

## Estrutura
- `index.php` — Home
- `cardapio.php` — catálogo e filtros
- `produto.php` — página individual
- `monte-seu-acai.php` — personalização
- `pedido.php` — checkout e pagamento na entrega
- `acompanhamento.php` — acompanhamento
- `login.php` / `cadastro.php` — autenticação do cliente
- `cliente/` — área do cliente
- `admin/` — painel administrativo
- `banco/banco.sql` — estrutura + dados iniciais
- `uploads/` — arquivos enviados pelo painel

## XAMPP
1. Copie a pasta `AcaiFlow` para `C:\xampp\htdocs\`.
2. Inicie Apache e MySQL.
3. Abra o phpMyAdmin e importe `banco/banco.sql`.
4. Acesse `http://localhost/AcaiFlow/`.

## Acesso administrativo inicial
- E-mail: `admin@acaiflow.local`
- Senha: `Admin@123`

Troque essa senha em produção.

## Estado desta versão
- Home (`index.php`) refinada para maior fidelidade ao wireframe Açaí Flow.
- Logo real recortada em PNG transparente disponível em `img/logo/logo-real.png`.
- Arte principal do produto em alta resolução disponível em `img/banners/hero-produto.png`.
- Componentes responsivos e navegação mobile implementados.
- Ícones principais da Home em SVG inline, sem dependência de biblioteca externa.
- PHP verificado com `php -l` em todos os arquivos existentes nesta versão.
- Rotas públicas principais testadas com servidor PHP local.

## Observação sobre as artes
Os wireframes fornecidos foram usados como referência de layout, cores, hierarquia e composição. Quando houver exportações oficiais de alta resolução para outras artes, substitua os arquivos correspondentes mantendo os mesmos caminhos para não alterar o PHP/CSS.


## Atualização de setembro de 2026
- Modo noturno com persistência via localStorage.
- Limites de personalização por tamanho/barca aplicados no front-end e validados no PHP.
- Adicional fixado em R$ 2,00 e limite de 1.
- Imagens sem crop forçado em cards e detalhes.
- Tipografia com antialiasing e renderização otimizada.

### Regras de personalização implementadas
- Copos: 180ml (1 acomp., 1 calda, 1 fruta), 200/300ml (2 acomp., 1 calda, 1 fruta), 400/500ml (3 acomp., 1 calda, 1 fruta), 700ml (4 acomp., 2 caldas, 1 fruta).
- Barcas: PP (3 acomp., 1 calda, 1 fruta, 500ml), P (4 acomp., 2 caldas, 2 frutas, 700ml), M (4 acomp., 2 caldas, 2 frutas, 1000ml), G (5 acomp., 3 caldas, 3 frutas, 1500ml), GG (5 acomp., 3 caldas, 4 frutas, 2000ml), XG (6 acomp., 4 caldas, 5 frutas, 3000ml).
- Creme: no máximo 1.
- Adicional: no máximo 1 e valor de R$ 2,00.
- As opções excedentes são desativadas visualmente e a mesma regra é validada novamente no PHP antes do pedido ser gravado.

## Revisão — 24/09/2026

- Modo noturno revisado e funcional em área pública e administrativa, com contraste e legibilidade maiores.
- `js/tema.js` passou a ser carregado no rodapé público e no painel administrativo.
- Cardápio reconstruído para exibir somente os tamanhos que existem no `Monte seu açaí`: 180, 200, 300, 400, 500 e 700 ml; barcas PP, P, M, G, GG e XG.
- Imagens do cardápio passaram a usar quadros com altura fixa e `object-fit: contain` para evitar imagens gigantes ou cortadas.
- Regras de personalização centralizadas em `config/limites.php` e aplicadas tanto no navegador quanto no PHP.
- Opções excedentes são bloqueadas automaticamente ao atingir o limite.
- A validação do pedido também rejeita limites excedidos no servidor, independentemente do navegador.
- Cartões de tamanho/barca do montador mostram apenas nome e preço; os limites aparecem no quadro de regras após a seleção.
- JavaScript e PHP revisados com validação de sintaxe.

## Banco de dados e segurança

A versão revisada inclui uma estrutura relacional para clientes, administradores, produtos, personalização, pedidos, histórico de status, endereços, contato, configurações e logs administrativos.

Credencial administrativa inicial: consulte `banco/README_BANCO.md`.

Para o XAMPP, a instalação pode começar com `root` sem senha. Para elevar a segurança, importe `banco/usuario_aplicacao.sql` e configure `config/local.php` a partir de `config/local.example.php`.

## Atualização — carrinho autenticado
- O carrinho possui persistência local e sincronização com a sessão PHP para clientes autenticados.
- Após cadastro/login, um carrinho que já existia no navegador é preservado e sincronizado com a sessão.
- O endpoint `processa/carrinho.php` valida CSRF e normaliza os dados antes de armazená-los na sessão.
- O checkout continua recalculando valores, estoque e limites no servidor; o carrinho do navegador nunca é tratado como fonte confiável para preço final.


### Atualização — combos, promoções e autenticação
- O `cardapio.php` agora possui uma área separada para Combos e Promoções.
- Combos continuam usando a categoria `Combos` existente; promoções continuam usando o campo `promocao` existente.
- Ambos podem receber início/fim de exibição no cadastro/edição do produto. Fora do período, deixam de aparecer na área especial do cardápio.
- A implementação usa a mesma tabela `produtos`, evitando duplicação de catálogo.
- Para bancos existentes, execute `banco/migracao_periodo_ofertas.sql`.
- O botão `Acesso administrativo` foi removido da área pública de login.
- As imagens genéricas das telas de login/cadastro foram removidas. O painel lateral ficou preparado para receber uma arte própria via `--auth-bg-image` em `css/login.css`, usando `background-size: cover`.
- O modo noturno foi reforçado também para login/cadastro, login administrativo, componentes claros e ícones do cabeçalho.


## Combos personalizáveis

A versão atual separa o produto do conjunto de regras de montagem dos combos. Em banco já existente, execute `banco/migracao_combos_personalizaveis.sql` depois da migração de período das ofertas. No painel, os produtos da categoria `Combos` possuem uma seção própria para configurar porções, formatos, tamanhos e limites de complementos.
