# Revisão responsiva — 28/09/2026

Correções pedidas: área do cliente (celular/tablet), imagem da página Sobre, menu ☰ do painel admin e varredura de outros erros responsivos.
Testado em 360, 390, 768, 1024 e 1366 px (Chromium + PHP 8.3 + MariaDB): 170 combinações de página × tamanho, 0 vazamentos horizontais e 0 erros de JavaScript.

## 1. Área do cliente — modelo próprio para tablet e celular
Causa do "site pela metade": a coluna `1fr` da grade crescia até caber o menu lateral inteiro (579 px numa tela de 390 px) e o `overflow-x:hidden` global cortava o resto. Agora usa `minmax(0,1fr)`.

- **Computador (>1024 px):** menu lateral, como antes.
- **Tablet (768–1024 px):** cartão da conta com botão Sair + abas segmentadas no topo, herói em linha, cards 2×2 (4 colunas acima de 900 px).
- **Celular (≤767 px):** cartão da conta + barra de abas fixa embaixo (Início, Pedidos, Meus dados, Montar). O rodapé do site reserva espaço para ela. O histórico de pedidos vira lista de cartões (sem rolagem lateral) e a linha de "Pedidos recentes" mostra o valor (antes ficava escondido).
- Textos que tinham 8–10 px foram aumentados nos modelos de tablet e celular.
- Menu único em `includes/cliente-nav.php` (antes copiado em cada página). Aceita `$clientActive` e `$clientName`.
- Tema escuro dos componentes novos e correção de 12 textos com contraste entre 1,1:1 e 1,5:1.

## 2. Página Sobre
A foto é 16:9 e o quadro só tinha `min-height`, por isso sobrava uma faixa bordô. O quadro agora tem `aspect-ratio:4/3` e a imagem é `position:absolute` com `object-fit:cover`. A foto "Sobre" da Home tinha o mesmo defeito e foi ajustada.

## 3. Painel administrativo
- O botão ☰ não tinha nenhum código em `admin.js`. Agora abre uma gaveta lateral (fundo escuro, botão ×, tecla Esc, fecha ao tocar num link, `aria-expanded`).
- A barra superior ficava por cima da gaveta (z-index); corrigido.
- Formulários (`.form-grid`) viravam 2 colunas no celular e passavam da tela; agora 1 coluna.
- KPIs de Contatos tinham `grid-template-columns` inline que impedia o responsivo.
- Tabelas largas ganham sombra nas bordas no celular indicando rolagem lateral.

## 4. Outros erros corrigidos
- Menu ☰ do site abria sobre o logo e os botões (tablet: 24 px; celular: 6 px). Agora ancorado em `top:100%`.
- `.mini-btn` ("Remover" no checkout, "Detalhes" na área do cliente) não tinha estilo no site público.
- Linha de pedidos: botão "Detalhes" quebrava para baixo (grade com 4 colunas para 5 itens).
- Monte seu açaí: nome e "Limite atingido" ficavam colados na mesma linha; no celular os complementos passam para 2 colunas (página de ~10.000 px para ~7.300 px).
- Contato > Outros canais: título e descrição estavam colados.
- Checkout no celular: formas de pagamento e botões maiores.

## 5. Fora do pedido (revisar se quiser reverter)
`js/pedido.js`: o checkout de cliente logado entrava em recursão (`render()` → `hydrate()` → evento → `render()`) e gerava "Maximum call stack size exceeded" no console. O erro existe no zip original. Foi adicionada uma trava de reentrada (variável `hydrating`, ~8 linhas no começo de `render()`). Testado com carrinho vazio, com itens, removendo item e recarregando.

## Arquivos
Alterados: `admin/_header.php`, `admin/contatos/index.php`, `cliente/index.php`, `cliente/meus-dados.php`, `cliente/meus-pedidos.php`, `cliente/pedido-detalhes.php`, `css/admin.css`, `css/cliente.css`, `css/responsivo.css`, `css/style.css`, `css/tema.css`, `includes/header.php`, `js/admin.js`, `js/pedido.js`.
Novo: `includes/cliente-nav.php`.
Nenhuma alteração no banco de dados.

## Não verificado / limites
- Aparelhos reais (principalmente iPhone).
- O botão coral principal (branco sobre coral) tem contraste 2,95:1; é a cor da marca e não foi alterado.
- Tabelas do admin continuam com rolagem lateral no celular (não viraram cartões).
- Alguns produtos do cardápio aparecem com quadro bege vazio; não foi checado se falta imagem no banco ou o arquivo.
- Pode haver seletores `.html.dark-mode` (com ponto a mais) em `admin.css`; só o de `cliente.css` foi corrigido.
- Se algum estilo antigo aparecer, force a atualização do navegador (Ctrl+F5).
